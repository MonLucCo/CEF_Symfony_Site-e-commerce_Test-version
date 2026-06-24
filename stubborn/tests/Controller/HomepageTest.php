<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomepageTest extends WebTestCase
{
    /**
     * Test 1 — La page d’accueil répond correctement
     */
    public function test_homepage_is_accessible()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $this->assertResponseIsSuccessful();
    }

    /**
     * Test 2 — Le menu visiteur contient les bonnes traductions (i18n)
     */
    public function test_homepage_menu_for_visitor_uses_correct_translations()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $translator = static::getContainer()->get('translator');

        $this->assertSelectorTextContains('nav', $translator->trans('menu.home'));
        $this->assertSelectorTextContains('nav', $translator->trans('menu.login'));
        $this->assertSelectorTextContains('nav', $translator->trans('menu.register'));
    }

    /**
     * Test 3 — Le menu visiteur contient les bonnes routes (structure HTML)
     */
    public function test_homepage_menu_for_visitor_contains_correct_routes()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        // Routes visiteurs
        $this->assertSelectorExists('a[href="/"]');          // app_home
        $this->assertSelectorExists('a[href="/login"]');     // app_login
        $this->assertSelectorExists('a[href="/register"]');  // app_register

        // Routes réservées aux utilisateurs connectés → ne doivent PAS apparaître
        $this->assertSelectorNotExists('a[href="/products"]'); // app_products
        $this->assertSelectorNotExists('a[href="/cart"]');     // app_cart_index
        $this->assertSelectorNotExists('a[href="/admin"]');    // app_admin
        $this->assertSelectorNotExists('a[href="/logout"]');   // app_logout
    }

    /**
     * Test 4 — La structure HTML de la page d’accueil est correcte
     */
    public function test_homepage_structure_is_correct()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        // Sections principales
        $this->assertSelectorExists('.home-hero');
        $this->assertSelectorExists('.home-about');

        // Section featured absente (pas de fixtures en test)
        $this->assertSelectorNotExists('.home-featured');
    }

    /**
     * Test 5 — Le contenu i18n de la page d’accueil est affiché
     */
    public function test_homepage_content_translations_are_displayed()
    {
        $client = static::createClient();
        $client->request('GET', '/');

        $translator = static::getContainer()->get('translator');

        // Hero
        $this->assertSelectorTextContains('h1', $translator->trans('home.hero.title'));
        $this->assertSelectorTextContains('p', $translator->trans('home.hero.slogan'));

        // About
        $this->assertSelectorTextContains('h2', $translator->trans('home.about.title'));
    }
}
