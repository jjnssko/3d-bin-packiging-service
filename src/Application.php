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
            if (false === array_key_exists('products', $inputData) || count($inputData['products']) === 0) {
                throw new \RuntimeException('Please provide products in json on input');
            }

            $products = $this->productService->getProductsForPackingApi(
                $inputData['products'] ?? []
            );

            if ($this->packingService->hasBeenInputDataAlreadyPacked($inputData)) {
                $boxAsArray = $this->packingService->getPackedBoxFromResponseByInputData($inputData);
                return $this->responseHandler->createResponse(200, $boxAsArray);
            }

            $boxes = $this->boxService->getBoxesForPacking();

            $response = $this->packingService->getPackShipmentResponseAsArray($products, $boxes);

            // It is necessary to check whether the products can be packed in one box
            $this->packingService->isOnlyOneBoxUsed($response['response']);

            $boxFromResponse = $this->packingService->getBoxFromResponse($response['response']);
            $box = $this->boxService->getBoxById($boxFromResponse['id']);

            $this->packingService->storePackingResult($inputData, $box, $response);
            return $this->responseHandler->createResponse(200, $box->toArray());
        } catch (GuzzleException $e) {
            return $this->handleFallbackLogic($inputData, $boxes, $products, $e->getMessage());
        } catch (\Throwable $e) {
            $error = is_string($e->getMessage())
                ? (json_decode($e->getMessage()) ?? [$e->getMessage()])
                : $e->getMessage();
            $this->packingService->storePackingResult($inputData, $box, $response, $error);
            return $this->responseHandler->createResponse(500, ['error' => $e->getMessage()]);
        }
    }

    /**
     * @param array $inputData
     * @param array<int, array{id: int, w: float, h: float, d: float, max_wg: float}> $boxes
     * @param array<int, array{id: int, w: float, h: float, d: float, wq: float, q: int}> $products
     * @param string $errorMessage
     * @return ResponseInterface
     */
    private function handleFallbackLogic(array $inputData, array $boxes, array $products, string $errorMessage): ResponseInterface
    {
        $products = $this->productService->getProductsForFallbackPacking($products);

        if (count($boxes)) {
            $error = ['No bins to pack', 'Fallback logic applied'];
            $this->packingService->storePackingResult($inputData, null, [$errorMessage], $error);
            return $this->responseHandler->createResponse(500, array_merge([$errorMessage], $error));
        }

        $chosenBox = $this->packagerPack($boxes, $products);

        if ($chosenBox === null) {
            $error = ['Products need multiple boxes to be packed', 'Fallback logic applied'];
            $this->packingService->storePackingResult($inputData, null, [$errorMessage], $error);
            return $this->responseHandler->createResponse(500, array_merge([$errorMessage], $error));
        }

        $box = $this->boxService->getBoxById($chosenBox->getId());
        $this->packingService->storePackingResult($inputData, $box, [$errorMessage], ['Fallback logic applied']);
        return $this->responseHandler->createResponse(200, $box->toArray());
    }

    private function packagerPack(array $boxes, array $products): ?Bin
    {
        $numberOfAvailableBoxes = count($boxes);
        if ($numberOfAvailableBoxes === 0) {
            return null;
        }

        $packager = new Packager(1, SortType::ASCENDING);

        /** @var array<int, array{id: int, w: float, h: float, d: float, max_wg: float}> $boxes */
        foreach ($boxes as $box) {
            $packager->addBin(new Bin($box['id'], $box['w'], $box['h'], $box['d'], $box['max_wg']));
        }

        /** @var array<int, array{id: string, w: float, h: float, d: float, wq: float}> $products */
        foreach ($products as $product) {
            $packager->addItem(new Item($product['id'], $product['w'], $product['h'], $product['d'], $product['wg']));
        }

        $packager->withFirstFit()->pack();

        /** @var Bin[] $bins */
        $bins = $packager->getBins();

        foreach ($bins as $key => $bin) {
            /** @var Item[] $fittedItems */
            $fittedItems = $bin->getFittedItems();
            /** @var Item[] $unfittedItems */
            $unfittedItems = $bin->getUnfittedItems();

            if (count($fittedItems) === count($products)) {
                return $bin;
            }

            if ($key < count($bins) && count($unfittedItems) > 0) {
                $boxes = array_filter($boxes, fn($box) => $box['id'] !== $bin->getId());
                return $this->packagerPack($boxes, $products);
            }

            if ($key === count($bins) && count($unfittedItems) === 0) {
                return $this->packagerPack($boxes, $products);
            }
        }

        return null;
    }

}
