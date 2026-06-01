<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    /**
     * Constante pour les tailles disponibles, utilisée pour la validation et l'affichage
     */
    public const SIZES = ['XS', 'S', 'M', 'L', 'XL'];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $name;

    #[Assert\PositiveOrZero]
    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private string $price;

    #[ORM\Column(length: 255)]
    private string $image;

    #[ORM\Column]
    private bool $isFeatured = false;

    #[Assert\PositiveOrZero]
    #[ORM\Column]
    private int $stockXS = 0;

    #[Assert\PositiveOrZero]
    #[ORM\Column]
    private int $stockS = 0;

    #[Assert\PositiveOrZero]
    #[ORM\Column]
    private int $stockM = 0;

    #[Assert\PositiveOrZero]
    #[ORM\Column]
    private int $stockL = 0;

    #[Assert\PositiveOrZero]
    #[ORM\Column]
    private int $stockXL = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    public function isFeatured(): ?bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): static
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }

    public function getStockXS(): ?int
    {
        return $this->stockXS;
    }

    public function setStockXS(int $stockXS): static
    {
        $this->stockXS = $stockXS;

        return $this;
    }

    public function getStockS(): ?int
    {
        return $this->stockS;
    }

    public function setStockS(int $stockS): static
    {
        $this->stockS = $stockS;

        return $this;
    }

    public function getStockM(): ?int
    {
        return $this->stockM;
    }

    public function setStockM(int $stockM): static
    {
        $this->stockM = $stockM;

        return $this;
    }

    public function getStockL(): ?int
    {
        return $this->stockL;
    }

    public function setStockL(int $stockL): static
    {
        $this->stockL = $stockL;

        return $this;
    }

    public function getStockXL(): ?int
    {
        return $this->stockXL;
    }

    public function setStockXL(int $stockXL): static
    {
        $this->stockXL = $stockXL;

        return $this;
    }

    public function getStockForSize(string $size): int
    {
        return match ($size) {
            'XS' => $this->getstockXS(),
            'S'  => $this->getstockS(),
            'M'  => $this->getstockM(),
            'L'  => $this->getstockL(),
            'XL' => $this->getstockXL(),
            default => 0,
        };
    }
}
