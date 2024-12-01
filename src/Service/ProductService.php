<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\PackagingResult;
use App\Normalizer\ProductNormalizer;

class ProductService
{
    /**
     * @param array<int, array<string, int|float>> $requestedProducts
     * @return array<int, array<string, int|float>>
     */
    public function getProductsForPacking(array $requestedProducts): array
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
}
