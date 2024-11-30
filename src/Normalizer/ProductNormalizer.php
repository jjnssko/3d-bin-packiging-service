<?php

declare(strict_types=1);

namespace App\Normalizer;

class ProductNormalizer
{
    /**
     * @param array{id: int, width: float, height: float, length: float, weight: float} $data
     * @return array<string, float|int>
     */
    public static function normalizeForClient(array $data): array
    {
        return [
            'id' => $data['id'],
            'w' => $data['width'],
            'h' => $data['height'],
            'd' => $data['length'],
            'wg' => $data['weight'],
            'q' => 1
        ];
    }
}