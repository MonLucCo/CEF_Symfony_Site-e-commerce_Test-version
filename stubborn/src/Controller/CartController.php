<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Debug : pour faire le lien entre les tests unitaires du service CartService et l'affichage du panier dans CartController
use App\Repository\ProductRepository;
use App\Controller\TestCartController;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        CartService $cartService,
        TestCartController $testCartController, // Debug : pour faire les tests de vérification du contenu du panier
        ProductRepository $productRepository    // Debug : pour faire les tests de vérification du contenu du panier
    ): Response {

        dump('CartController::index - Appel du service CartService'); // Debug : pour vérifier que la méthode est bien appelée

        $items = $cartService->getDetailedCart();
        $total = $cartService->getTotal($items);

        dump('CartController::index - Fin d\'appel du service CartService'); // Debug : pour fin d'appel du service

        $testData = $testCartController->getTestData($productRepository);   // Debug : pour faire les tests de vérification du contenu du panier

        return $this->render('cart/index.html.twig', [
            'items' => $items,
            'total' => $total,
            'testData' => $testData,    // Debug : pour faire les tests de vérification du contenu du panier
        ]);
    }

    #[Route('/add/{id}/{size}', name: 'add', methods: ['POST'])]
    public function add(int $id, string $size, CartService $cartService): Response
    {
        $cartService->add($id, $size);

        $this->addFlash('success', 'cart.flash.added');

        return $this->redirectToRoute('cart_index', [], 303);   // 303 : redirection après POST (PRG pattern)
    }

    #[Route('/decrease/{id}/{size}', name: 'decrease', methods: ['POST'])]
    public function decrease(int $id, string $size, CartService $cartService): Response
    {
        $cartService->decrease($id, $size);

        return $this->redirectToRoute('cart_index', [], 303);   // 303 : redirection après POST (PRG pattern)
    }

    #[Route('/remove/{id}/{size}', name: 'remove', methods: ['POST'])]
    public function remove(int $id, string $size, CartService $cartService): Response
    {
        // Debug : pour vérifier que les paramètres sont bien reçus
        dump($id, $size);

        $cartService->remove($id, $size);

        $this->addFlash('info', 'cart.flash.removed');

        return $this->redirectToRoute('cart_index', [], 303);   // 303 : redirection après POST (PRG pattern)
    }

    #[Route('/clear', name: 'clear', methods: ['POST'])]
    public function clear(CartService $cartService): Response
    {
        $cartService->clear();

        $this->addFlash('warning', 'cart.flash.cleared');

        return $this->redirectToRoute('cart_index', [], 303);   // 303 : redirection après POST (PRG pattern)
    }
}
