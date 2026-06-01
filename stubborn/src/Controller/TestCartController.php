<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CartState
{
    public function __construct(
        public array $items,
        public float $total
    ) {}
}

class TestCartData
{
    public function __construct(
        public array $products,
        public array $sizes
    ) {}
}

class TestCartController extends AbstractController
{
    private function statusCart(String $message, CartService $cartService): CartState
    {
        $items = $cartService->getDetailedCart();
        $total = $cartService->getTotal($items);

        dump($message, new CartState($items, $total));

        return new CartState($items, $total);
    }

    public function getTestData(ProductRepository $productRepository): TestCartData
    {
        return new TestCartData(
            products: $productRepository->findAll(),
            sizes: \App\Entity\Product::SIZES
        );
    }

    #[Route('/test/cart', name: 'test_app_cart')]
    public function test(CartService $cartService): Response
    {
        // Test 1 : clear
        $cartService->clear();
        $state = $this->statusCart('Test 1 : clear', $cartService);

        // Test 2 : ajout
        $cartService->add(22, 'M');
        $cartService->add(22, 'M');
        $cartService->add(25, 'S');
        $cartService->add(27, 'L');
        $state = $this->statusCart('Test 2 : ajout', $cartService);

        // Test 3 : suppression
        $cartService->remove(25, 'S');
        $state = $this->statusCart('Test 3 : suppression', $cartService);

        // Test 4 : rajout d'une autre taille
        $cartService->add(22, 'S');
        $state = $this->statusCart('Test 4 : rajout d\'une autre taille', $cartService);

        // return $this->render('test/cart.html.twig');
        return $this->render('cart/index.html.twig', [
            'items' => $state->items,
            'total' => $state->total,
        ]);
    }
}
