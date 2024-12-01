<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\PackagingResult;
use Doctrine\ORM\NonUniqueResultException;

/**
 * @method PackagingResult findOneById(int $id)
 */
class PackagingResultRepository extends BaseRepository
{
    /** @throws NonUniqueResultException */
    public function findByInputHash(string $inputHash): ?PackagingResult
    {
        $queryBuilder = $this->createQueryBuilder('pr');
        $queryBuilder->where('pr.inputHash = :inputHash')
            ->andWhere('pr.box IS NOT NULL')
            ->setParameter('inputHash', $inputHash);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }
}
