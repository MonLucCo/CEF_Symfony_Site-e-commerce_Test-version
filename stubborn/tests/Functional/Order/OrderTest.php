<?php

namespace App\Tests\Functional\Order;

use App\Tests\Functional\WebTestCaseBase;

class OrderTest extends WebTestCaseBase
{
    /* ============================================================
       Helpers spécifiques aux tests d’achat
       ============================================================ */

    private function goToCheckout(): void
    {
        $crawler = $this->client->request('GET', '/cart' . '/');
        $link = $crawler->filter('[data-test="cart-btn-checkout"]')->link();
        $this->crawler = $this->client->click($link);
    }

    private function simulateStripeSuccess(): void
    {
        $this->crawler = $this->client->request('GET', '/order/success?session_id=dummy');
    }

    private function simulateStripeCancel(): void
    {
        $this->crawler = $this->client->request('GET', '/order/cancel?session_id=dummy');
    }

    /* ============================================================
       ACH-01 : Accès à /checkout selon le rôle et la vérification
       ============================================================ */
    public function test_ACH_01_checkout_access_control_refused()
    {
        // 1) Non connecté → login
        $this->crawler = $this->client->request('GET', '/order/checkout');
        $this->assertResponseRedirects('/login');

        // 2) Admin → refus
        $this->loginAsAdmin();
        $this->crawler = $this->client->request('GET', '/order/checkout');
        $this->assertResponseRedirects('/admin/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.flash-error', $this->t(key: 'admin.flash.order_forbidden', domain: 'admin'));

        // 3) User non vérifié → refus
        $this->loginAsUnverifiedUser();
        $this->crawler = $this->client->request('GET', '/order/checkout');
        $this->assertResponseRedirects('/account/not-verified');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.flash-error', $this->t('order.flash.user_not_verified'));
    }

    /* ============================================================
       ACH-02 : Panier vide → impossible de commander
       ============================================================ */
    public function test_ACH_02_empty_cart_cannot_checkout()
    {
        $this->loginAsUser();
        $this->crawler = $this->client->request('GET', '/order/checkout');
        $this->assertResponseRedirects('/cart' . '/');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.flash-warning', $this->t('order.flash.empty_quantities'));
        $this->logoutTest();
    }

    /* ============================================================
       ACH-03 : Quantités invalides → checkout bloqué
       ============================================================ */
    public function test_ACH_03_invalid_quantities_block_checkout()
    {
        $this->loginAsUser();

        // Ajouter un produit
        $this->addProduct(1, 'M');

        // Diminuer jusqu'à 0
        $this->decreaseProduct(1, 'M');

        // Essayer de commander
        $this->crawler = $this->client->request('GET', '/order/checkout');
        $this->assertResponseRedirects('/cart' . '/');
        $this->client->followRedirect();

        $this->assertSelectorExists('.flash-warning');
        $this->assertSelectorTextContains('.flash-warning', $this->t('order.flash.empty_quantities'));
        $this->logoutTest();
    }

    /* ============================================================
       ACH-04 : Création PaymentIntent Stripe
       ============================================================ */
    public function test_ACH_04_payment_intent_created()
    {
        $this->loginAsUser();
        $this->addProduct(1, 'M');

        // Aller au checkout via l’UI
        $this->goToCheckout();

        // Vérifier qu’on a bien une redirection
        $this->assertResponseRedirects();

        // Vérifier que l’URL de redirection pointe vers Stripe
        $location = $this->client->getResponse()->headers->get('Location');
        $this->assertNotNull($location);
        $this->assertStringStartsWith('https://checkout.stripe.com', $location);
    }

    /* ============================================================
       ACH-05 : Retour Stripe success → panier vidé + email envoyé
       ============================================================ */
    public function test_ACH_05_success_clears_cart_and_sends_email()
    {
        // 1) User vérifié + panier
        $this->loginAsUser();
        $this->addProduct(1, 'M');

        // 2) Simuler retour Stripe success
        $this->simulateStripeSuccess();

        // 3) Vérifier que la page success s'affiche
        $this->assertSelectorTextContains('h1', $this->t('order.success.title'));

        // 4) Cliquer sur le bouton d’envoi d’email
        $form = $this->crawler->filter('[data-test="order-btn-email"]')->form();
        $this->crawler = $this->client->click($form);

        // 5) Vérifier redirection vers le shop
        $this->assertResponseRedirects('/products');
        $this->client->followRedirect();
        $this->assertSelectorTextContains('.flash-success', $this->t('order.flash.success_order'));

        // 6) Vérifier que le panier est vidé
        $this->visit('/cart' . '/');
        $this->assertSelectorExists('.cart-empty');
    }

    /* ============================================================
       ACH-06 : Retour Stripe cancel → panier intact
       ============================================================ */
    public function test_ACH_06_cancel_keeps_cart()
    {
        $this->loginAsUser();

        $this->addProduct(1, 'M');

        $this->simulateStripeCancel();

        // Panier toujours présent
        $this->visit('/cart' . '/');
        $this->assertSelectorExists('table.table tbody tr');
    }
}
