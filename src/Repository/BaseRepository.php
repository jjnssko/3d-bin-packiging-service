<?php

declare(strict_types=1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class BaseRepository extends EntityRepository
{
    public function save(object $object): void
    {
        $this->_em->persist($object);
        $this->_em->flush();
    }

    public function findOneById(int $id)
    {
        return $this->findOneBy(['id' => $id]);
    }
}
