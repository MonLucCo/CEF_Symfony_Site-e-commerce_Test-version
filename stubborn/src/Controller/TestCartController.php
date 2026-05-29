<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestCartController extends AbstractController
{
    #[Route('/test/cart', name: 'test_cart')]
    public function test(CartService $cartService): Response
    {
        // Test 1 : ajout
        $cartService->add(22, 'M');
        $cartService->add(22, 'M');
        $cartService->add(27, 'L');

        dump($cartService->getDetailedCart());
        dump($cartService->getTotal());

        // Test 2 : suppression
        $cartService->remove(22);
        dump($cartService->getDetailedCart());
        dump($cartService->getTotal());

        // Test 3 : clear
        $cartService->clear();
        dump($cartService->getDetailedCart());
        dump($cartService->getTotal());

        return $this->render('test/cart.html.twig');
    }
}
