<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PackagingResultRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackagingResultRepository::class)]
class PackagingResult
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $response;

    #[ORM\Column(type: Types::TEXT)]
    private string $inputHash;

    #[ORM\Column(type: Types::JSON)]
    private array $inputData;

    #[ORM\ManyToOne(targetEntity: Box::class)]
    #[ORM\JoinColumn(name: 'box_id', referencedColumnName: 'id')]
    private Box|null $box = null;

    #[ORM\Column(type: "datetime")]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): PackagingResult
    {
        $this->id = $id;
        return $this;
    }

    public function getResponse(): string
    {
        return $this->response;
    }

    public function setResponse(string $response): PackagingResult
    {
        $this->response = $response;
        return $this;
    }

    public function getInputHash(): string
    {
        return $this->inputHash;
    }

    public function setInputHash(string $inputHash): PackagingResult
    {
        $this->inputHash = $inputHash;
        return $this;
    }

    public function setInputHashFromInputData(array $inputData): PackagingResult
    {
        $this->inputHash = $this->generateInputHash($inputData);
        return $this;
    }

    public function getInputData(): array
    {
        return $this->inputData;
    }

    public function setInputData(array $inputData): PackagingResult
    {
        $this->inputData = $inputData;
        return $this;
    }

    public function getBox(): ?Box
    {
        return $this->box;
    }

    public function setBox(?Box $box): PackagingResult
    {
        $this->box = $box;
        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public static function generateInputHash(array $inputData): string
    {
        return hash('sha256', json_encode($inputData));
    }
}
