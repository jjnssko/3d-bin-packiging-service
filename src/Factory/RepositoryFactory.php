<?php

namespace App\Factory;

use App\Entity\Box;
use App\Entity\PackagingResult;
use App\Repository\BoxRepository;
use App\Repository\PackagingResultRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\NotSupported;

readonly class RepositoryFactory
{
    public function __construct(private EntityManager $entityManager) {}

    /** @throws NotSupported */
    public function getBoxRepository(): BoxRepository|EntityRepository
    {
        return $this->entityManager->getRepository(Box::class);
    }

    /** @throws NotSupported */
    public function getPackagingResultRepository(): PackagingResultRepository|EntityRepository
    {
        return $this->entityManager->getRepository(PackagingResult::class);
    }
}
