<?php

namespace App\Service;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductService
{
    public function __construct(
        private EntityManagerInterface $em,
        private ProductRepository $repo,
        private string $projectDir
    ) {}

    /**
     * Création d’un produit
     */
    public function create(Product $product, ?UploadedFile $file): void
    {
        $filename = $this->handleImageUpload($file);
        $product->setImage($filename);

        $this->em->persist($product);
        $this->em->flush();
    }

    /**
     * Mise à jour d’un produit
     */
    public function update(Product $product, ?UploadedFile $file): void
    {
        if ($file) {
            $this->deleteImage($product->getImage());
            $filename = $this->handleImageUpload($file);
            $product->setImage($filename);
        }

        $this->em->flush();
    }

    /**
     * Suppression d’un produit
     */
    public function delete(Product $product): void
    {
        $this->deleteImage($product->getImage());
        $this->em->remove($product);
        $this->em->flush();
    }

    /**
     * Mise en avant / retrait
     */
    public function toggleFeatured(Product $product): bool
    {
        if (!$product->isFeatured()) {
            if ($this->repo->countFeatured() >= Product::MAX_FEATURED) {
                return false;
            }
            $product->setIsFeatured(true);
        } else {
            $product->setIsFeatured(false);
        }

        $this->em->flush();
        return true;
    }

    /**
     * Upload ou copie du placeholder
     */
    private function handleImageUpload(?UploadedFile $file): string
    {
        $uploadDir = $this->projectDir . '/public/uploads/products';

        if ($file) {
            $filename = uniqid('prod_') . '.' . $file->guessExtension();
            $file->move($uploadDir, $filename);
            return $filename;
        }

        // Aucun fichier → copie du placeholder
        $placeholder = $this->projectDir . '/public/uploads/no-image.jpeg';
        $filename = uniqid('prod_') . '.jpeg';
        copy($placeholder, $uploadDir . '/' . $filename);

        return $filename;
    }

    /**
     * Suppression d’une image
     */
    private function deleteImage(string $filename): void
    {
        $path = $this->projectDir . '/public/uploads/products/' . $filename;

        if (file_exists($path)) {
            unlink($path);
        }
    }
}
