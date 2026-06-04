<?php

namespace App\Controller;

use App\Service\CartService;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/order/checkout', name: 'order_checkout')]
    public function createCheckoutSession(
        StripeService $stripeService,
        CartService $cartService
    ): Response {

        // Contrôle des accès direct
        // Empêcher les admins de passer commande
        if ($this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'order.flash.admin_forbidden');
            return $this->redirectToRoute('app_home');
        }
        // Vérifier que l'utilisateur est connecté et vérifié
        if (!$this->getUser()) {
            $this->addFlash('error', 'order.flash.login_required');
            return $this->redirectToRoute('app_home');
        }
        if (!$this->isGranted('IS_VERIFIED')) {
            $this->addFlash('error', 'order.flash.user_not_verified');
            return $this->redirectToRoute('app_account_not_verified');
        }

        $lineCartItems = $cartService->getDetailedCart();

        // 1) Vérifier que le panier contient au moins une quantité > 0
        $hasPositiveQuantity = false;
        foreach ($lineCartItems as $item) {
            if ($item['quantity'] > 0) {
                $hasPositiveQuantity = true;
                break;
            }
        }

        if (!$hasPositiveQuantity) {
            $this->addFlash('warning', 'order.flash.empty_quantities');
            return $this->redirectToRoute('app_cart_index');
        }

        // 2) Appel Stripe
        $session = $stripeService->createCheckoutSession($lineCartItems);
        return $this->redirect($session->url);
    }

    #[Route('/order/success', name: 'order_success')]
    public function success(): Response
    {
        // Contrôle des accès direct
        // Vérifier que l'utilisateur est connecté et vérifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        if (!$this->isGranted('IS_VERIFIED')) {
            return $this->redirectToRoute('app_account_not_verified');
        }

        return $this->render('order/success.html.twig');
    }

    #[Route('/order/cancel', name: 'order_cancel')]
    public function cancel(): Response
    {
        // Contrôle des accès direct
        // Vérifier que l'utilisateur est connecté et vérifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        if (!$this->isGranted('IS_VERIFIED')) {
            return $this->redirectToRoute('app_account_not_verified');
        }

        return $this->render('order/cancel.html.twig');
    }
}
