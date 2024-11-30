<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Box;
use App\Entity\PackagingResult;

/**
 * @method PackagingResult findOneById(int $id)
 */
class PackagingResultRepository extends BaseRepository
{
    public function findByInputHash(string $inputHash): ?PackagingResult
    {
        return $this->findOneBy(['inputHash' => $inputHash]);
    }

    public function storeResponse(string $response, array $inputData, ?Box $box): void
    {
        $packagingResult = (new PackagingResult())
            ->setResponse($response)
            ->setInputHashFromInputData($inputData)
            ->setInputData($inputData)
            ->setBox($box);

        $this->save($packagingResult);
    }
}
