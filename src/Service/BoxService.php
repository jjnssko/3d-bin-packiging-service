<?php

namespace App\Service;

use App\Entity\Box;
use App\Normalizer\BoxNormalizer;
use App\Repository\BoxRepository;

readonly class BoxService
{
    public function __construct(private BoxRepository $boxRepository) {}

    /** @return array<int, array{id: int, w: float, h: float, d: float, max_wg: float}> */
    public function getBoxesForPacking(): array
    {
        $boxObjects = $this->boxRepository->findAllBoxes();

        $boxes = [];
        foreach ($boxObjects as $box) {
            $boxes[] = BoxNormalizer::normalizeForClient($box);
        }

        return $boxes;
    }

    public function getBoxById(int $id): Box
    {
        $box = $this->boxRepository->findOneById($id);

        if (null === $box) {
            throw new \RuntimeException('Box does not exist');
        }

        return $box;
    }
}
