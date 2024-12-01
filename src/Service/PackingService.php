<?php

declare(strict_types=1);

namespace App\Service;

use App\Client\Packing3dBinClient;
use App\Entity\Box;
use App\Entity\PackagingResult;
use App\Repository\PackagingResultRepository;
use Doctrine\ORM\NonUniqueResultException;
use GuzzleHttp\Exception\GuzzleException;

readonly class PackingService
{
    public function __construct(
        private Packing3dBinClient $packingClient,
        private PackagingResultRepository $packagingResultRepository,
    ) {}

    /** @throws GuzzleException */
    public function getPackShipmentResponseAsArray(array $products, array $boxes): array
    {
        $response = $this->packingClient->packShipment($products, $boxes);
        $response = json_decode($response->getBody()->getContents(), true);

        if (count($response['response']['errors']) > 0) {
            throw new \RuntimeException(json_encode($response['response']['errors']));
        }

        return $response;
    }

    public function getBoxFromResponse(array $response): array
    {
        return $response['bins_packed'][0]['bin_data'];
    }

    /** @throws NonUniqueResultException */
    public function hasBeenInputDataAlreadyPacked(array $products): bool
    {
        $inputHash = PackagingResult::generateInputHash($products);
        $packagingResult = $this->packagingResultRepository->findByInputHash($inputHash);

        return $packagingResult !== null;
    }

    /** @throws NonUniqueResultException */
    public function getPackedBoxFromResponseByInputData(array $inputData): array
    {
        $inputHash = PackagingResult::generateInputHash($inputData);
        $packagingResult = $this->packagingResultRepository->findByInputHash($inputHash);

        if (null === $packagingResult) {
            throw new \RuntimeException('Valid packing process with provided input data was not found');
        }

        return $packagingResult->getBox()->toArray();
    }

    public function isOnlyOneBoxUsed(array $response): void
    {
        if (count($response['bins_packed']) > 1) {
            throw new \RuntimeException('Products need multiple boxes to be packed');
        }
    }

    public function storePackingResult(array $inputData, ?Box $box, ?array $response = [], ?array $error = null): void
    {
        $packagingResult = (new PackagingResult())
            ->setInputData($inputData)
            ->setBox($box)
            ->setResponse($response)
            ->setError($error);

        $this->packagingResultRepository->save($packagingResult);
    }
}
