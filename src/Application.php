<?php

namespace App;

use App\Client\Packing3dBinClient;
use App\Entity\Box;
use App\Entity\PackagingResult;
use App\Normalizer\ProductNormalizer;
use App\Repository\BoxRepository;
use App\Repository\PackagingResultRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Application
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** @throws NotSupported|GuzzleException */
    public function run(RequestInterface $request): ResponseInterface
    {
        /** @var BoxRepository $boxRepository */
        $boxRepository = $this->entityManager->getRepository(Box::class);
        /** @var PackagingResultRepository $packagingResultRepository */
        $packagingResultRepository = $this->entityManager->getRepository(PackagingResult::class);

        $boxes = $boxRepository->getNormalizedDataForPack();

        $payload = $request->getBody()->getContents();
        $requestedProducts = json_decode($payload, true)['products'];
        $products = [];
        foreach ($requestedProducts as $product) {
            $products[] = ProductNormalizer::normalizeForClient($product);
        }

        $packagingResult = $packagingResultRepository->findByInputHash(PackagingResult::generateInputHash($products));
        if (null !== $packagingResult) {
            return new Response(200, [], $packagingResult->getResponse());
        }

        $packingClient = new Packing3dBinClient();

        try {
            $response = $packingClient->packShipment($products, $boxes);
            $response = $response->getBody()->getContents();
            $responseData = json_decode($response, true)['response'];

            $numberOfBoxes = count($responseData['bins_packed']);
            $numberOfErrors = count($responseData['errors']);

            if ($numberOfBoxes > 1 || $numberOfErrors > 0) {
                $packagingResultRepository->storeResponse($response, $products, null);
                throw new \RuntimeException(json_encode($responseData['errors']), 500);
            }

            $binId = $responseData['bins_packed'][0]['bin_data']['id'];
            $box = $boxRepository->findOneById($binId);
            $packagingResultRepository->storeResponse($response, $products, $box);

            return new Response(200, ['Content-Type' => 'application/json'], $response);
        } catch (\Throwable $e) {
            return new Response(500, [], $e->getMessage());
        }
    }
}
