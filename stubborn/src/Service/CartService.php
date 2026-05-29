<?php

namespace App\Service;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private ?SessionInterface $session;
    private ProductRepository $productRepository;

    public function __construct(RequestStack $requestStack, ProductRepository $productRepository)
    {
        $this->session = $requestStack->getSession();
        $this->productRepository = $productRepository;
    }

    private function getCart(): array
    {
        return $this->session->get('cart', []);
    }

    private function saveCart(array $cart): void
    {
        $this->session->set('cart', $cart);
    }

    public function add(int $productId, string $size): void
    {
        $cart = $this->getCart();

        // Si le produit existe déjà avec la même taille → quantité++
        if (isset($cart[$productId]) && $cart[$productId]['size'] === $size) {
            $cart[$productId]['quantity']++;
        } else {
            // Sinon → nouvelle entrée
            $cart[$productId] = [
                'size' => $size,
                'quantity' => 1
            ];
        }

        $this->saveCart($cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->getCart();

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
        }

        $this->saveCart($cart);
    }

    public function clear(): void
    {
        $this->session->remove('cart');
    }

    public function getDetailedCart(): array
    {
        $cart = $this->getCart();
        $detailedCart = [];

        foreach ($cart as $productId => $item) {
            $product = $this->productRepository->find($productId);

            if (!$product) {
                continue; // produit supprimé en base
            }

            $detailedCart[] = [
                'product' => $product,
                'size' => $item['size'],
                'quantity' => $item['quantity'],
                'total' => $product->getPrice() * $item['quantity']
            ];
        }

        return $detailedCart;
    }

    public function getTotal(): float
    {
        $total = 0;

        foreach ($this->getDetailedCart() as $item) {
            $total += $item['total'];
        }

        return $total;
    }
}
