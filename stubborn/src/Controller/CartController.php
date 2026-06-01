<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

// Debug : pour faire le lien entre les tests unitaires du service CartService et l'affichage du panier dans CartController
use App\Repository\ProductRepository;
use App\Controller\TestCartController;

#[Route('/cart', name: 'app_cart_')]
class CartController extends AbstractController
{
    private function verifyCsrfToken(Request $request, string $tokenId): ?Response
    {
        $token = $request->request->get('_token');

        if (!$this->isCsrfTokenValid($tokenId, $token)) {
            $this->addFlash('error', 'cart.csrf.invalid');
            throw $this->createAccessDeniedException('cart.csrf.invalid');
        }

        return null; // ✔ Token valide → on laisse l’action continuer
    }

    private function redirectAfterAction(Request $request): Response
    {
        $redirectTo = $request->request->get('redirectTo');

        // 1) Si un redirectTo est présent dans la requête → priorité
        if ($redirectTo) {
            $this->addFlash('debug', 'Redirection vers : ' . $redirectTo);  // Debug : pour vérifier la valeur de redirectTo
            return $this->redirect($redirectTo, 303);
        }

        // 2) Sinon → fallback vers le panier
        $this->addFlash('debug', 'Fallback vers le panier');    // Debug : pour vérifier que le fallback est utilisé
        return $this->redirectToRoute('app_cart_index', [], 303);
    }

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
    public function add(int $id, string $size, CartService $cartService, Request $request, TranslatorInterface $translator): Response
    {
        if ($response = $this->verifyCsrfToken($request, 'app_cart_add')) {
            return $response; // ✔ renvoie la page 403 si token CSRF invalide
        }

        $cartService->add($id, $size);

        $this->addFlash('success', 'cart.flash.added');

        return $this->redirectAfterAction($request);
    }

    #[Route('/decrease/{id}/{size}', name: 'decrease', methods: ['POST'])]
    public function decrease(int $id, string $size, CartService $cartService, Request $request, TranslatorInterface $translator): Response
    {
        if ($response = $this->verifyCsrfToken($request, 'app_cart_decrease')) {
            return $response; // ✔ renvoie la page 403 si token CSRF invalide
        }

        $cartService->decrease($id, $size);

        return $this->redirectAfterAction($request);
    }

    #[Route('/remove/{id}/{size}', name: 'remove', methods: ['POST'])]
    public function remove(int $id, string $size, CartService $cartService, Request $request, TranslatorInterface $translator): Response
    {
        if ($response = $this->verifyCsrfToken($request, 'app_cart_remove')) {
            return $response; // ✔ renvoie la page 403 si token CSRF invalide
        }

        // Debug : pour vérifier que les paramètres sont bien reçus
        dump($id, $size);

        $cartService->remove($id, $size);

        $this->addFlash('info', 'cart.flash.removed');

        return $this->redirectAfterAction($request);
    }

    #[Route('/clear', name: 'clear', methods: ['POST'])]
    public function clear(CartService $cartService, Request $request, TranslatorInterface $translator): Response
    {

        if ($response = $this->verifyCsrfToken($request, 'app_cart_clear')) {
            return $response; // ✔ renvoie la page 403 si token CSRF invalide
        }

        $cartService->clear();

        $this->addFlash('warning', 'cart.flash.cleared');

        return $this->redirectAfterAction($request);
    }
}
