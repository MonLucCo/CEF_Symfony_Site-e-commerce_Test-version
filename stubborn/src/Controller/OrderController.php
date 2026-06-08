<?php

namespace App\Controller;

use App\Service\OrderService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/order/checkout', name: 'order_checkout')]
    public function checkout(
        OrderService $orderService
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

        $user = $this->getUser();

        try {
            $items = $orderService->prepareCheckout($user);
        } catch (\RuntimeException $e) {
            if ($e->getMessage() === 'empty_cart') {
                $this->addFlash('warning', 'order.flash.empty_quantities');
                return $this->redirectToRoute('app_cart_index');
            }
            throw $e; // Re-throw unexpected exceptions
        }

        // 2) Appel Stripe
        $session = $orderService->createStripeSession($items);
        return $this->redirect($session->url);
    }

    #[Route('/order/success', name: 'order_success')]
    public function success(
        OrderService $orderService
    ): Response {
        // Contrôle des accès direct
        // Vérifier que l'utilisateur est connecté et vérifié
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_home');
        }
        if (!$this->isGranted('IS_VERIFIED')) {
            return $this->redirectToRoute('app_account_not_verified');
        }

        $user = $this->getUser();
        $orderService->processSuccess($user);

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

    #[Route('/order/send-confirmation', name: 'order_send_confirmation')]
    public function sendConfirmation(
        OrderService $orderService
    ): Response {

        if (!$this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $user = $this->getUser();
        $orderService->sendConfirmationEmail($user);

        $this->addFlash('success', 'order.flash.success_order');

        return $this->redirectToRoute('app_products');
    }
}
