<?php

namespace App\Tests\Functional\Cart;

use App\Tests\Functional\WebTestCaseBase;

class CartTest extends WebTestCaseBase
{

    /** CRT-01 : accès au panier → redirection → login → accès autorisé */
    public function test_CRT_01_cart_is_accessible()
    {
        // 1) Accès non authentifié → redirection vers /login
        $this->client->request('GET', '/cart');
        $this->assertResponseRedirects('/login');

        // 2) Suivre la redirection
        $crawler = $this->client->followRedirect();

        // 3) Soumettre le formulaire de connexion
        $form = $crawler->filter('form')->form([
            '_username' => 'client@test.com',
            '_password' => 'password'
        ]);
        $this->client->submit($form);

        // 4) Vérifier redirection après login
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        // 5) Accéder au panier maintenant authentifié
        $this->visit('/cart' . '/'); // ici visit() est OK
        $this->assertSelectorTextContains('h1', $this->t('cart.title'));
    }

    /** CRT-02 : panier vide + retour boutique */
    public function test_CRT_02_empty_cart_and_back_to_shop()
    {
        // 1) Aller sur /login
        $this->visit('/login');

        // 2) Soumettre le formulaire de connexion
        $form = $this->client->getCrawler()->filter('form')->form([
            '_username' => 'client@test.com',
            '_password' => 'password'
        ]);
        $this->client->submit($form);

        // 3) Vérifier redirection après login
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        // 4) Accéder au panier
        $this->client->clickLink($this->t('menu.cart'));
        $this->assertResponseIsSuccessful();

        // 5) Vérifier panier vide
        $this->assertSelectorExists('.cart-empty');

        // 6) Vérifier lien retour boutique
        $this->assertSelectorExists('a[href="/products"]');

        // 7) Vérifier absence du bouton commande
        $this->assertSelectorNotExists('.btn-primary');
    }

    /** CRT-03 : ajouter un produit, voir bouton commande, vider panier */
    public function test_CRT_03_add_product_then_clear()
    {
        $this->loginAsUser();

        // Ajouter un produit
        $this->addProduct(1, 'M');

        // Flash success
        $this->assertSelectorExists('.flash-success');

        // Aller au panier
        $this->visit('/cart' . '/');

        // Une ligne de produit doit exister
        $this->assertSelectorExists('table.table tbody tr');

        // Bouton "Finaliser ma commande" présent
        $this->assertSelectorExists('.cart-actions a.btn-primary');

        // Vider le panier
        $this->clearCart();

        // Panier vide
        // $this->visit('/cart' . '/');
        $this->assertSelectorExists('.cart-empty');
    }

    /** CRT-04 : panier avec 1 produit (quantités, total, suppression) */
    public function test_CRT_04_cart_with_one_product()
    {
        // Connexion
        $this->loginAsUser();

        // Ajout d'un produit (page détail du produit)
        $this->addProduct(1, 'M');

        // Aller au panier
        $this->visit('/cart' . '/');

        // Ligne du produit existe
        $this->assertSelectorExists('table.table tbody tr');

        // Augmenter la quantité en ajoutant un produit (page détail du produit)
        $this->addProduct(1, 'M');

        // Aller au panier
        $this->visit('/cart' . '/');

        // Quantité "2" dans le bloc quantité
        $this->assertSelectorTextContains('.cart-qty span', '2');

        // Diminuer la quantité
        $this->decreaseProduct(1, 'M');

        $this->assertSelectorTextContains('.cart-qty span', '1');

        // Supprimer le produit
        $this->removeProduct(1, 'M');

        // Panier vide
        $this->assertSelectorExists('.cart-empty');

        // Pas de bouton "Finaliser ma commande"
        $this->assertSelectorNotExists('.cart-actions a.btn-primary');
    }

    /** CRT-05 : panier avec 2 produits, suppression progressive */
    public function test_CRT_05_cart_with_two_products()
    {
        // Connexion
        $this->loginAsUser();

        // Ajout de deux produits différents
        $this->addProduct(1, 'M');
        $this->addProduct(2, 'M');

        // Aller au panier
        $this->visit('/cart' . '/');

        // Deux lignes de produit
        $this->assertEquals(
            2,
            $this->client->getCrawler()->filter('table.table tbody tr')->count()
        );

        // Bouton "Finaliser ma commande" présent
        $this->assertSelectorExists('.cart-actions a.btn-primary');

        // Lien "Continuer mes achats" présent
        $this->assertSelectorExists('a[href="/products"]');

        // Supprimer le premier produit
        $this->removeProduct(1, 'M');
        $this->visit('/cart' . '/');

        // Il reste une ligne
        $this->assertEquals(
            1,
            $this->client->getCrawler()->filter('table.table tbody tr')->count()
        );

        // Bouton commande toujours présent
        $this->assertSelectorExists('.cart-actions a.btn-primary');

        // Supprimer le second produit
        $this->removeProduct(2, 'M');
        $this->visit('/cart' . '/');

        // Panier vide
        $this->assertSelectorExists('.cart-empty');

        // Lien "Continuer mes achats" toujours présent
        $this->assertSelectorExists('a[href="/products"]');

        // Plus de bouton commande
        $this->assertSelectorNotExists('.cart-actions a.btn-primary');
    }

    /** CRT-06 : vérification des lignes et des totaux (ligne + panier) */
    public function test_CRT_06_totals_are_correct()
    {
        $this->loginAsUser();

        // Ajouter deux produits
        $this->addProduct(1, 'M');
        $this->addProduct(1, 'M'); // quantité = 2
        $this->addProduct(2, 'L'); // quantité = 1

        // Aller au panier
        $this->visit('/cart' . '/');

        // Lire les lignes
        $rows = $this->readCartTable();

        // Vérifier cohérence ligne par ligne
        foreach ($rows as $row) {
            $this->assertEquals(
                $row['unit'] * $row['qty'],
                $row['line'],
                "Le total de la ligne est incorrect."
            );
        }

        // Vérifier total du panier
        $expectedTotal = array_sum(array_column($rows, 'line'));
        $actualTotal = $this->readCartTotal();

        $this->assertEquals($expectedTotal, $actualTotal, "Le total du panier est incorrect.");

        // Trace du calcul
        $this->markMessageTestId(id: "CRT-06 : ", message: "Total calculé : " . $expectedTotal . " €", newline: true);
    }

    /** CRT-07 : vérification des totaux après modifications (decrease/remove/add) */
    public function test_CRT_07_totals_after_modifications()
    {
        $this->loginAsUser();

        // Ajouts initiaux
        $this->addProduct(1, 'M');
        $this->addProduct(1, 'M');
        $this->addProduct(2, 'L');

        // Modifications
        $this->addProduct(4, 'M');
        $this->addProduct(4, 'S');
        $this->addProduct(3, 'L');
        $this->decreaseProduct(1, 'M');
        $this->removeProduct(2, 'L');
        $this->addProduct(3, 'S');

        // Vérification
        $this->visit('/cart' . '/');
        $rows = $this->readCartTable();

        $expected = array_sum(array_column($rows, 'line'));
        $actual = $this->readCartTotal();

        $this->assertEquals($expected, $actual, "Le total du panier est incorrect après modifications.");

        // Trace du calcul
        $this->markMessageTestId(id: "CRT-07 : ", message: "Total calculé : " . $expected . " €", newline: true);
    }
}
