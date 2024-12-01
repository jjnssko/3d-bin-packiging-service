<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PackagingResult;
use App\Normalizer\ProductNormalizer;

class ProductService
{
    /**
     * @param array<int, array{id: int, width: float, height: float, length: float, weight: float}> $requestedProducts
     * @return array<int, array<string, int|float>>
     */
    public function getProductsForPackingApi(array $requestedProducts): array
    {
        $normalizedProducts = [];

        foreach ($requestedProducts as $product) {
            $normalized = ProductNormalizer::normalizeForClient($product);
            $hash = PackagingResult::generateInputHash($product);

            if (isset($normalizedProducts[$hash])) {
                $normalizedProducts[$hash]['q']++;
            } else {
                $normalizedProducts[$hash] = $normalized;
            }
        }

        return array_values($normalizedProducts);
    }
    /**
     * @param array<int, array{id: int, w: float, h: float, d: float, wq: float}> $normalizedProducts
     * @return array<int, array{id: string, w: float, h: float, d: float, wq: float}>
     */
    public function getProductsForFallbackPacking(array $normalizedProducts): array
    {
        $modifiedProducts = [];

        foreach ($normalizedProducts as $key => $product) {
            $quantity = $product['q'];
            if ($quantity === 1) {
                unset($normalizedProducts[$key]['q']);
                $modifiedProducts[] = $normalizedProducts[$key];
                continue;
            }
            for ($i = 1; $i <= $quantity; $i++) {
                $modifiedProducts[] = [
                    'id' => sprintf('%d-%d', $product['id'], $i),
                    'w' => $product['w'],
                    'h' => $product['h'],
                    'd' => $product['d'],
                    'wg' => $product['wg'],
                ];
            }
        }

        return array_values($modifiedProducts);
    }
}
