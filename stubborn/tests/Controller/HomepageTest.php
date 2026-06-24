<?php

namespace App\Tests\Controller;

use App\Repository\ProductRepository;
use App\Tests\Functional\WebTestCaseBase;

class HomepageTest extends WebTestCaseBase
{
    public function test_HOM_01_homepage_is_accessible()
    {
        $this->visit('/');
    }

    public function test_HOM_02_homepage_menu_for_visitor_uses_correct_translations()
    {
        $this->visit('/');

        $this->assertSelectorTextContains('nav', $this->t('menu.home'));
        $this->assertSelectorTextContains('nav', $this->t('menu.login'));
        $this->assertSelectorTextContains('nav', $this->t('menu.register'));
    }

    public function test_HOM_03_homepage_menu_for_visitor_contains_correct_routes()
    {
        $this->visit('/');

        $this->assertLinkExists('/');
        $this->assertLinkExists('/login');
        $this->assertLinkExists('/register');

        $this->assertLinkNotExists('/products');
        $this->assertLinkNotExists('/cart');
        $this->assertLinkNotExists('/admin');
        $this->assertLinkNotExists('/logout');
    }

    public function test_HOM_04_homepage_structure_is_correct()
    {
        $this->visit('/');

        $this->assertSelectorExists('.home-hero');
        $this->assertSelectorExists('.home-about');

        $repo = static::getContainer()->get(ProductRepository::class);
        $featured = $repo->findBy(['isFeatured' => true]);

        if ($featured) {
            $this->markMessageTestId(id: "HOM-04-01", message: "Featured expected: " . count($featured));

            $this->assertSelectorCount(count($featured), '.home-featured .card');
            $this->assertSelectorExists('.home-featured');
        } else {
            $this->markMessageTestId(id: "HOM-04-01", message: "No featured expected");

            $this->assertSelectorNotExists('.home-featured');
        }
    }

    public function test_HOM_05_homepage_content_translations_are_displayed()
    {
        $this->visit('/');

        $this->assertSelectorTextContains('h1', $this->t('home.hero.title'));
        $this->assertSelectorTextContains('p', $this->t('home.hero.slogan'));
        $this->assertSelectorTextContains('h2', $this->t('home.about.title'));
    }
}
