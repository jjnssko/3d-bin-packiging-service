<?php

namespace App;

use App\Service\BoxService;
use App\Service\PackingService;
use App\Service\ProductService;
use App\Service\ResponseHandler;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

readonly class Application
{
    public function __construct(
        private PackingService  $packingService,
        private ProductService  $productService,
        private BoxService      $boxService,
        private ResponseHandler $responseHandler
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
        } catch (\Throwable $e) {
            $error = is_string($e->getMessage())
                ? (json_decode($e->getMessage()) ?? [$e->getMessage()])
                : $e->getMessage();
            $this->packingService->storePackingResult($inputData, $box, $response, $error);
            return $this->responseHandler->createResponse(500, ['error' => $e->getMessage()]);
        }
    }
}
