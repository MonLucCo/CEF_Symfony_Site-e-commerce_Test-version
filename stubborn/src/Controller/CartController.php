<?php

namespace App\Controller;

use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

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

        return null; // Token valide → on laisse l’action continuer
    }

    private function redirectAfterAction(Request $request): Response
    {
        $redirectTo = $request->request->get('redirectTo');

        // 1) Si un redirectTo est présent dans la requête → priorité
        if ($redirectTo) {
            return $this->redirect($redirectTo, 303);
        }

        // 2) Sinon → fallback vers le panier
        return $this->redirectToRoute('app_cart_index', [], 303);
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(
        CartService $cartService,
    ): Response {

        $items = $cartService->getDetailedCart();
        $total = $cartService->getTotal($items);


        return $this->render('cart/index.html.twig', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    public function add(CartService $cartService, Request $request, TranslatorInterface $translator): Response
    {
        if ($response = $this->verifyCsrfToken($request, 'app_cart_add')) {
            return $response; // renvoie la page 403 si token CSRF invalide
        }

        $id = $request->request->get('id');
        $size = $request->request->get('size');

        try {
            $cartStatus = $cartService->add($id, $size);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'cart.flash.internal_error');
            return $this->redirectAfterAction($request);
        }

        switch ($cartStatus) {
            case 'added':
                $this->addFlash('success', 'cart.flash.added');
                break;

            case 'stock_limit_reached':
                $this->addFlash('warning', 'cart.flash.stock_limit');
                break;

            case 'invalid_size':
            case 'product_not_found':
            case 'size_not_available':
                $this->addFlash('error', 'cart.flash.not_added');
                break;

            default:
                $this->addFlash('error', 'cart.flash.internal_error');
                break;
        }

        return $this->redirectAfterAction($request);
    }

    #[Route('/decrease', name: 'decrease', methods: ['POST'])]
    public function decrease(CartService $cartService, Request $request, TranslatorInterface $translator): Response
    {
        if ($response = $this->verifyCsrfToken($request, 'app_cart_decrease')) {
            return $response; // renvoie la page 403 si token CSRF invalide
        }

        $id = $request->request->get('id');
        $size = $request->request->get('size');

        try {
            $result = $cartService->decrease($id, $size);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'cart.flash.internal_error');
            return $this->redirectAfterAction($request);
        }

        switch ($result['status']) {

            case 'decreased':
                $this->addFlash(
                    'info',
                    $translator->trans('cart.flash.decreased', [
                        '%quantity%' => $result['quantity']
                    ])
                );
                break;

            case 'quantity_already_zero':
                $this->addFlash('warning', 'cart.flash.quantity_zero');
                break;

            case 'product_not_in_cart':
            case 'size_not_in_cart':
                $this->addFlash('error', 'cart.flash.not_added');
                break;

            default:
                $this->addFlash('error', 'cart.flash.internal_error');
                break;
        }

        return $this->redirectAfterAction($request);
    }

    #[Route('/remove', name: 'remove', methods: ['POST'])]
    public function remove(CartService $cartService, Request $request, TranslatorInterface $translator): Response
    {
        if ($response = $this->verifyCsrfToken($request, 'app_cart_remove')) {
            return $response; // renvoie la page 403 si token CSRF invalide
        }

        $id = $request->request->get('id');
        $size = $request->request->get('size');

        try {
            $status = $cartService->remove($id, $size);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'cart.flash.internal_error');
            return $this->redirectAfterAction($request);
        }

        switch ($status) {
            case 'removed':
                // Récupération du nom du produit pour l’UX
                $product = $cartService->getProduct($id);
                $this->addFlash(
                    'warning',
                    $translator->trans('cart.flash.removed_named', [
                        '%product%' => $product->getName(),
                        '%size%' => $size
                    ])
                );
                break;

            case 'product_not_in_cart':
            case 'size_not_in_cart':
                $this->addFlash('error', 'cart.flash.not_added');
                break;

            default:
                $this->addFlash('error', 'cart.flash.internal_error');
                break;
        }

        return $this->redirectAfterAction($request);
    }

    #[Route('/clear', name: 'clear', methods: ['POST'])]
    public function clear(CartService $cartService, Request $request, TranslatorInterface $translator): Response
    {

        if ($response = $this->verifyCsrfToken($request, 'app_cart_clear')) {
            return $response; // renvoie la page 403 si token CSRF invalide
        }

        try {
            $status = $cartService->clear();
        } catch (\Throwable $e) {
            $this->addFlash('error', 'cart.flash.internal_error');
            return $this->redirectAfterAction($request);
        }

        if ($status === 'cleared') {
            $this->addFlash('warning', 'cart.flash.cleared');
        } else {
            $this->addFlash('error', 'cart.flash.internal_error');
        }

        return $this->redirectAfterAction($request);
    }
}
