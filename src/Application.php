<?php

namespace App;

use App\Service\BoxService;
use App\Service\PackingService;
use App\Service\ProductService;
use App\Service\ResponseHandler;
use GuzzleHttp\Exception\GuzzleException;
use Latuconsinafr\BinPackager\BinPackager3D\Bin;
use Latuconsinafr\BinPackager\BinPackager3D\Item;
use Latuconsinafr\BinPackager\BinPackager3D\Packager;
use Latuconsinafr\BinPackager\BinPackager3D\Types\SortType;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

readonly class Application
{
    public function __construct(
        private PackingService  $packingService,
        private ProductService  $productService,
        private BoxService      $boxService,
        private ResponseHandler $responseHandler,
    ) {
    }

    public function run(RequestInterface $request): ResponseInterface
    {
        $inputData = json_decode($request->getBody()->getContents(), true) ?? [];
        $box = null;
        $response = [];

        try {
            if (false === array_key_exists('products', $inputData)) {
                throw new \RuntimeException('Please provide products in json on input');
            }

            $products = $this->productService->getProductsForPacking(
                $inputData['products'] ?? []
            );

            if ($this->packingService->hasBeenInputDataAlreadyPacked($inputData)) {
                $boxFromResponse = $this->packingService->getPackedBoxFromResponseByInputData($inputData);
                return $this->responseHandler->createResponse(200, $boxFromResponse);
            }

            $boxes = $this->boxService->getBoxesForPacking();

            $response = $this->packingService->getPackShipmentResponseAsArray($products, $boxes);

            // It is necessary to check whether the products can be packed in one box
            $this->packingService->isOnlyOneBoxUsed($response['response']);

            $boxFromResponse = $this->packingService->getBoxFromResponse($response['response']);
            $box = $this->boxService->getBoxById($boxFromResponse['id']);

            $this->packingService->storePackingResult($inputData, $box, $response);
            return $this->responseHandler->createResponse(200, $boxFromResponse);
        } catch (GuzzleException $e) {
            $packager = new Packager(1, SortType::ASCENDING);
            $box = null;
            /** @var array<int, array{id: int, w: float, h: float, d: float, max_wg: float}> $boxes */
            foreach ($boxes as $box) {
                $packager->addBin(new Bin($box['id'], $box['w'], $box['h'], $box['d'], $box['max_wg']));
            }

            /** @var array<int, array{id: int, w: float, h: float, d: float, wq: float, q: int}> $products */
            $products = $this->productService->getProductsForFallbackPacking($products);
            /** @var array<int, array{id: string, w: float, h: float, d: float, wq: float}> $products */
            foreach ($products as $product) {
                $packager->addItem(new Item($product['id'], $product['w'], $product['h'], $product['d'], $product['wg']));
            }

            $packager->withFirstFit()->pack();

            $bins = $packager->getBins();

            $chosenBox = null;
            /**
             * @var int $key
             * @var Bin $bin
             */
            foreach ($bins as $key => $bin) {
                if ($bin->getFittedItems() === count($products)) {
                    $chosenBox = $bin;
                    break;
                }

                if ($key < count($bins) && ($bin->getFittedItems() === 0 || $bin->getUnfittedItems())) {
                    $boxes = array_filter($boxes, fn($box) => $box['id'] !== $bin->getId());
                }

                if ($key === count($bins) && $bin->getFittedItems() === 0) {
                    // recursion
                }
            }

            if (null === $chosenBox) {
                $this->packingService->storePackingResult($inputData, $box, [$e->getMessage()], ['Products need multiple boxes to be packed', 'Fallback logic applied']);
                return $this->responseHandler->createResponse(500, ['error' => $e->getMessage()]);
            }

            $box = $this->boxService->getBoxById($chosenBox->getId());
            $this->packingService->storePackingResult($inputData, $box, [$e->getMessage()], ['Fallback logic applied']);
            return $this->responseHandler->createResponse(200, '$boxFromResponse');
        } catch (\Throwable $e) {
            $error = is_string($e->getMessage())
                ? (json_decode($e->getMessage()) ?? [$e->getMessage()])
                : $e->getMessage();
            $this->packingService->storePackingResult($inputData, $box, $response, $error);
            return $this->responseHandler->createResponse(500, ['error' => $e->getMessage()]);
        }
    }
}
