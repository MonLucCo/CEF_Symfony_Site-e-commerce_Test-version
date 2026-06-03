<?php

namespace App\Controller;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OrderController extends AbstractController
{
    #[Route('/order/create-checkout-session', name: 'order_checkout')]
    public function createCheckoutSession(StripeService $stripeService): Response
    {
        // Données statiques pour l’étape 2
        $lineItems = [
            [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => 'Produit de test',
                    ],
                    'unit_amount' => 1999, // 19,99 €
                ],
                'quantity' => 1,
            ]
        ];

        $session = $stripeService->createCheckoutSession($lineItems);

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
