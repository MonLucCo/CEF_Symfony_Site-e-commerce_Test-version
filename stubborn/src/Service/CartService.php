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

    public function add(int $productId, string $size): void
    {
        // Validation de la taille
        if (!in_array($size, Product::SIZES, true)) {
            return; // ou throw une exception si tu veux être strict
        }

        $cart = $this->getCart();
        $product = $this->productRepository->find($productId);

        if (!$product) {
            return;
        }

        // Vérification du stock pour cette taille
        $maxStock = $product->getStockForSize($size);
        if ($maxStock === null) {
            return;
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
        }

        $this->saveCart($cart);
    }

    public function decrease(int $id, string $size): void
    {
        $cart =  $this->getCart();

        if (!isset($cart[$id][$size])) {
            return;
        }

        // Diminuer la quantité
        if ($cart[$id][$size]['quantity'] > 0) {
            $cart[$id][$size]['quantity']--;
        }

        // On NE supprime PAS la ligne ici
        // min = 0, c’est tout

        $this->saveCart($cart);
    }

    public function remove(int $productId, string $size): void
    {
        $cart = $this->getCart();

        // Si l'entrée n'existe pas, on ne touche à rien
        if (!isset($cart[$productId][$size])) {
            return;
        }

        unset($cart[$productId][$size]);

        // Si plus aucune taille pour ce produit → supprimer le produit
        if (empty($cart[$productId])) {
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

        dump('getDetailedCart : ' . json_encode($cart)); // Debug : pour vérifier la structure du panier

        foreach ($cart as $productId => $sizes) {
            $product = $this->productRepository->find($productId);

            dump('Boucle getDetailedCart : ' . $productId .
                ' - ' . json_encode($sizes) .
                ' # {' . $product->getId() . ' - ' . $product->getName() . '}'); // Debug : pour vérifier le produit trouvé

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

                dump('Item ajouté au detailedCart : ' . json_encode(end($detailedCart))); // Debug : pour vérifier les items ajoutés

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
}
