<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Entity\Box;

class BoxNormalizer
{
    /** @return array{id: int, w: float, h: float, d: float, max_wg: float} */
    public static function normalizeForClient(Box $data): array
    {
        return [
            'id' => (int) $data->getId(),
            'w' => $data->getWidth(),
            'h' => $data->getHeight(),
            'd' => $data->getLength(),
            'max_wg' => $data->getMaxWeight(),
        ];
    }
}
