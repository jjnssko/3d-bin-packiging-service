<?php

declare(strict_types=1);

namespace App\Service;

use App\Normalizer\ProductNormalizer;

class ProductService
{
    public function getProductsForPacking(array $requestedProducts): array
    {
        return array_map(
            fn($product) => ProductNormalizer::normalizeForClient($product),
            $requestedProducts
        );
    }
}
