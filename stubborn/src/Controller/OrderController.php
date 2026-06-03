<?php

namespace App\Controller;

use App\Service\CartService;
use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/order/create-checkout-session', name: 'order_checkout')]
    public function createCheckoutSession(
        StripeService $stripeService,
        CartService $cartService
    ): Response {
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
        return $this->render('order/success.html.twig');
    }

    #[Route('/order/cancel', name: 'order_cancel')]
    public function cancel(): Response
    {
        return $this->render('order/cancel.html.twig');
    }
}
