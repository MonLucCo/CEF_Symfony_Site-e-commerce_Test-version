<?php

namespace App\Service;

use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeService
{
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(UrlGeneratorInterface $urlGenerator)
    {
        $this->urlGenerator = $urlGenerator;

        // Charger la clé secrète Stripe
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    /**
     * Crée une session Stripe Checkout
     */
    public function createCheckoutSession(array $lineItems): Session
    {
        // Docs :Stripe\Checkout\Session::create() - https://stripe.com/docs/api/checkout/sessions/create#create_checkout_session-locale
        $allowedLocales = ['auto', 'fr', 'en', 'es', 'de', 'it', 'nl', 'pt', 'sv', 'da', 'fi', 'no', 'ja', 'zh'];
        $locale = $_ENV['APP_DEFAULT_LOCALE'] ?? 'auto';

        if (!in_array($locale, $allowedLocales, true)) {
            $locale = 'auto';
        }

        return Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => $this->urlGenerator->generate('order_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->urlGenerator->generate('order_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'locale' => $locale,
        ]);
    }
}
