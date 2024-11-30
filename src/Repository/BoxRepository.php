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
    /** @return array<int, array<string, float>> */
    public function getNormalizedDataForPack(): array
    {
        /** @var Box[] $boxes */
        $boxObjects = $this->findAll();

        $boxes = [];
        foreach ($boxObjects as $box) {
            $boxes[] = BoxNormalizer::normalizeForClient($box);
        }

        return $boxes;
    }
}
