<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Box;
use App\Normalizer\BoxNormalizer;

/**
 * @method Box findOneById(int $id)
 */
class BoxRepository extends BaseRepository
{
    /** @return Box[] */
    public function findAllBoxes(): array
    {
        /** @var Box[] $boxes */
        return $this->findAll();
    }
}
