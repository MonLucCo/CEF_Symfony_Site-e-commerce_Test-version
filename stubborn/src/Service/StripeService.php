<?php

namespace App\Service;

use App\Service\CartService;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class StripeService
{
    private UrlGeneratorInterface $urlGenerator;
    private CartService $cartService;

    private TranslatorInterface $translator;

    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        CartService $cartService,
        TranslatorInterface $translator
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->cartService = $cartService;
        $this->translator = $translator;

        // Charger la clé secrète Stripe
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    /**
     * Crée une session Stripe Checkout
     */
    public function createCheckoutSession(array $lineCartItems): Session
    {
        $lineStripeItems = $this->getStripeLineItems($lineCartItems);

        if (empty($lineStripeItems)) {
            throw new \InvalidArgumentException($this->translator->trans('cart.flash.empty_quantities'));
        }

        // Docs :Stripe\Checkout\Session::create() - https://stripe.com/docs/api/checkout/sessions/create#create_checkout_session-locale
        $allowedLocales = ['auto', 'fr', 'en', 'es', 'de', 'it', 'nl', 'pt', 'sv', 'da', 'fi', 'no', 'ja', 'zh'];
        $locale = $_ENV['APP_DEFAULT_LOCALE'] ?? 'auto';

        if (!in_array($locale, $allowedLocales, true)) {
            $locale = 'auto';
        }

        return Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => $lineStripeItems,
            'success_url' => $this->urlGenerator->generate('order_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->urlGenerator->generate('order_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'locale' => $locale,
        ]);
    }

    /**
     * Récupère les éléments de ligne Stripe
     */
    public function getStripeLineItems(array $lineCartItems): array
    {
        $items = [];

        foreach ($lineCartItems as $cartItem) {
            $product = $cartItem['product'];
            $quantity = $cartItem['quantity'];
            $size = $cartItem['size'];

            if ($quantity < 1) {
                continue; // Ignorer les quantités nulles ou négatives
            }

            $translatedSize = sprintf(
                $this->translator->trans(
                    'order.stripe.size',
                    ['%size%' => $size]
                )
            );
            $productName = $product->getName() . ' - ' . $translatedSize;

            $items[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $productName,
                    ],
                    'unit_amount' => $product->getPrice() * 100, // Stripe = centimes
                ],
                'quantity' => $quantity,
            ];
        }

        return $items;
    }
}
