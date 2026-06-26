<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

abstract class WebTestCaseBase extends WebTestCase
{
    protected $client;
    protected $crawler;
    protected $translator;
    protected ContainerInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        // Client HTTP réutilisable
        $this->client = static::createClient();

        // Container Symfony
        $this->container = static::getContainer();

        // Service de traduction
        $this->translator = $this->container->get('translator');
    }

    /**
     * Charge une page et met à jour le crawler.
     */
    protected function visit(string $url, int $expectedStatus = Response::HTTP_OK): void
    {
        $this->crawler = $this->client->request('GET', $url);

        $this->assertResponseStatusCodeSame(
            $expectedStatus,
            sprintf('La page "%s" devrait retourner %s.', $url, $expectedStatus)
        );
    }

    /**
     * Raccourci pour traduire une clé i18n.
     */
    protected function t(
        string $key,
        array $parameters = [],
        string $domain = 'messages'
    ): string {
        return $this->translator->trans($key, $parameters, $domain);
    }

    /**
     * Vérifie qu’un lien existe.
     */
    protected function assertLinkExists(string $href): void
    {
        $this->assertSelectorExists('a[href="' . $href . '"]');
    }

    /**
     * Vérifie qu’un lien n’existe pas.
     */
    protected function assertLinkNotExists(string $href): void
    {
        $this->assertSelectorNotExists('a[href="' . $href . '"]');
    }

    /**
     * Récupère un service du container.
     */
    protected function getService(string $id)
    {
        return $this->container->get($id);
    }

    /**
     * Affiche un message formaté dans le terminal
     */
    protected function markMessageTestId(string $id, string $message, bool $newline = false): void
    {
        $prefix = $newline ? "\n" : '';
        fwrite(STDOUT, $prefix . "[$id] >> " . $message . "\n");
    }

    /**
     * Connecte un utilisateur (cf. tests/Fixtures/AppTestFixtures.php) : Utilisateur vérifié
     */
    protected function loginAsUser(
        string $email = 'client@test.com',
        string $password = 'password'
    ): Crawler {
        // 1) Aller sur /login
        $crawler = $this->client->request('GET', '/login');

        // 2) Soumettre le formulaire
        $form = $crawler->filter('form')->form([
            '_username' => $email,
            '_password' => $password
        ]);
        $this->client->submit($form);

        // 3) Vérifier redirection après login
        $this->assertResponseRedirects('/');

        // 4) Suivre la redirection et retourner le crawler
        return $this->client->followRedirect();
    }

    /**
     * Connecte un administrateur (cf. tests/Fixtures/AppTestFixtures.php) : Admin  + Utilisateur non-vérifié
     */
    protected function loginAsAdmin(
        string $email = 'admin@test.com',
        string $password = 'password'
    ): Crawler {
        // 1) Aller sur /login
        $crawler = $this->client->request('GET', '/login');

        // 2) Soumettre le formulaire
        $form = $crawler->filter('form')->form([
            '_username' => $email,
            '_password' => $password
        ]);
        $this->client->submit($form);

        // 3) Vérifier redirection après login
        $this->assertResponseRedirects('/admin/');

        // 4) Suivre la redirection et retourner le crawler
        return $this->client->followRedirect();
    }

    /**
     * Connecte un utilisateur non vérifié (cf. tests/Fixtures/AppTestFixtures.php) : Utilisateur non-vérifié
     */
    protected function loginAsUnverifiedUser(
        string $email = 'nouveau@test.com',
        string $password = 'password'
    ): Crawler {
        // 1) Aller sur /login
        $crawler = $this->client->request('GET', '/login');

        // 2) Soumettre le formulaire
        $form = $crawler->filter('form')->form([
            '_username' => $email,
            '_password' => $password
        ]);
        $this->client->submit($form);

        // 3) Vérifier redirection après login
        $this->assertResponseRedirects('/');

        // 4) Suivre la redirection et retourner le crawler
        return $this->client->followRedirect();
    }

    /**
     * Déconnecte de l'application
     */
    protected function logoutTest(): Crawler
    {
        // 1)Vérifie que le menu connecté apparaît
        $this->assertSelectorExists('a[href="/logout"]');

        // 2) client le menu déconnexion
        $this->client->clickLink($this->t('menu.logout'));

        // 3) Vérifier redirection vers la page d'accueil
        $this->assertResponseRedirects('/');

        // 4) Suivre la redirection
        $crawler = $this->client->followRedirect();

        // /5) Vérifie que le menu visiteur apparaît
        $this->assertSelectorExists('a[href="/login"]');

        return $crawler;
    }

    /**
     * Ajoute un produit au Panier
     */
    protected function addProduct(int $id, string $size): void
    {
        // 1) Page produit
        $crawler = $this->client->request('GET', "/product/$id");

        // 2) Récupérer le formulaire
        $form = $crawler->selectButton($this->t('product.add_to_cart'))->form();

        // 3) Modifier uniquement la taille
        $form['size'] = $size;

        // 4) Soumettre
        $this->client->submit($form);

        // 5) Suivre la redirection
        $this->client->followRedirect();
    }

    /**
     * Dimininue la quantité d'un produit du Panier
     */
    protected function decreaseProduct(int $id, string $size): void
    {
        $crawler = $this->client->request('GET', '/cart/');

        // cibler le bon formulaire
        $form = $crawler->filter("form[action='/cart/decrease']")
            ->reduce(function ($node) use ($id, $size) {
                return $node->filter("input[name='id'][value='$id']")->count() &&
                    $node->filter("input[name='size'][value='$size']")->count();
            })
            ->form();

        $this->client->submit($form);
        $this->client->followRedirect();
    }

    /**
     * Supprime un produit du Panier
     */
    protected function removeProduct(int $id, string $size): void
    {
        $crawler = $this->client->request('GET', '/cart/');

        $form = $crawler->filter("form[action='/cart/remove']")
            ->reduce(function ($node) use ($id, $size) {
                return $node->filter("input[name='id'][value='$id']")->count() &&
                    $node->filter("input[name='size'][value='$size']")->count();
            })
            ->form();

        $this->client->submit($form);
        $this->client->followRedirect();
    }

    /**
     * Vide le Panier
     */
    protected function clearCart(): void
    {
        $crawler = $this->client->request('GET', '/cart/');

        $form = $crawler->filter("form[action='/cart/clear']")->form();

        $this->client->submit($form);
        $this->client->followRedirect();
    }

    /**
     * Mémorise le contenu du Panier
     */
    protected function readCartTable(): array
    {
        $crawler = $this->client->getCrawler();

        $rows = $crawler->filter('table.table tbody tr');

        $data = [];

        foreach ($rows as $i => $row) {
            $rowCrawler = $rows->eq($i);

            $unit = $rowCrawler->filter('td:nth-child(3)')->text();   // prix unitaire
            $qty  = $rowCrawler->filter('.cart-qty span')->text();    // quantité
            $line = $rowCrawler->filter('td:nth-child(5)')->text();   // total ligne

            $data[] = [
                'unit' => floatval(str_replace(',', '.', preg_replace('/[^0-9,]/', '', $unit))),
                'qty'  => intval($qty),
                'line' => floatval(str_replace(',', '.', preg_replace('/[^0-9,]/', '', $line))),
            ];
        }

        return $data;
    }

    /**
     * Mémorise le total du Panier
     */
    protected function readCartTotal(): float
    {
        $text = $this->client->getCrawler()->filter('.cart-total strong')->text();
        return floatval(str_replace(',', '.', preg_replace('/[^0-9,]/', '', $text)));
    }
}
