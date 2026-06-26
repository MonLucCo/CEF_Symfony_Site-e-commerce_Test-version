<?php

namespace App\Service;

use App\Entity\Product;
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

    public function add(int $productId, string $size): string
    {
        // Validation de la taille
        if (!in_array($size, Product::SIZES, true)) {
            return 'invalid_size';
        }

        $cart = $this->getCart();
        $product = $this->productRepository->find($productId);

        if (!$product) {
            return 'product_not_found';
        }

        // Vérification du stock pour cette taille
        $maxStock = $product->getStockForSize($size);
        if ($maxStock === null) {
            return 'size_not_available';
        }

        // Si le produit n'existe pas encore dans le panier
        if (!isset($cart[$productId])) {
            $cart[$productId] = [];
        }

        // Si la taille n'existe pas encore
        if (!isset($cart[$productId][$size])) {
            $cart[$productId][$size] = ['quantity' => 0];
        }

        // Quantité actuelle
        $currentQty = $cart[$productId][$size]['quantity'];

        // Vérification du stock
        if ($currentQty < $maxStock) {
            $cart[$productId][$size]['quantity']++;
        } else return 'stock_limit_reached';

        $this->saveCart($cart);

        return 'added';
    }

    public function decrease(int $id, string $size): array
    {
        $cart =  $this->getCart();

        if (!isset($cart[$id])) {
            return ['status' => 'product_not_in_cart'];
        }

        if (!isset($cart[$id][$size])) {
            return ['status' => 'size_not_in_cart'];
        }

        $currentQty = $cart[$id][$size]['quantity'];

        if ($currentQty === 0) {
            return [
                'status' => 'quantity_already_zero',
                'quantity' => 0
            ];
        }

        // Diminuer la quantité
        $cart[$id][$size]['quantity']--;

        $this->saveCart($cart);

        return [
            'status' => 'decreased',
            'quantity' => $cart[$id][$size]['quantity']
        ];
    }

    public function remove(int $productId, string $size): string
    {
        $cart = $this->getCart();

        // Si l'entrée n'existe pas, on ne touche à rien
        if (!isset($cart[$productId])) {
            return 'product_not_in_cart';
        }

        if (!isset($cart[$productId][$size])) {
            return 'size_not_in_cart';
        }

        unset($cart[$productId][$size]);

        // Si plus aucune taille pour ce produit → supprimer le produit
        if (empty($cart[$productId])) {
            unset($cart[$productId]);
        }

        $this->saveCart($cart);

        return 'removed';
    }

    public function clear(): string
    {
        $this->session->remove('cart');

        return 'cleared';
    }

    public function getDetailedCart(): array
    {
        $cart = $this->getCart();
        $detailedCart = [];

        foreach ($cart as $productId => $sizes) {
            $product = $this->productRepository->find($productId);

            if (!$product) {
                continue;
            }

            foreach ($sizes as $size => $data) {
                $quantity = $data['quantity'];

                $detailedCart[] = [
                    'product' => $product,
                    'size' => $size,
                    'price' => $product->getPrice(),
                    'quantity' => $quantity,
                    'total' => $product->getPrice() * $quantity,
                ];
            }
        }

        return $detailedCart;
    }

    public function getTotal(array $items): float
    {
        $total = 0;

        foreach ($items as $item) {
            $total += $item['total'];
        }

        return $total;
    }

    public function saveLastOrder(array $items, float $total): void
    {
        $this->session->set('last_order_items', $items);
        $this->session->set('last_order_total', $total);
    }

    public function getLastOrderItems(): array
    {
        return $this->session->get('last_order_items', []);
    }

    public function getLastOrderTotal(): float
    {
        return $this->session->get('last_order_total', 0);
    }

    public function clearLastOrder(): void
    {
        $this->session->remove('last_order_items');
        $this->session->remove('last_order_total');
    }

    public function getProduct(int $id): ?Product
    {
        return $this->productRepository->find($id);
    }
}
