<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
    protected function t(string $key): string
    {
        return $this->translator->trans($key);
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
     * Connecte un utilisateur (cf. tests/Fixtures/AppTestFixtures.php)
     */
    protected function loginAsUser(
        string $email = 'client@test.com',
        string $password = 'password'
    ): \Symfony\Component\DomCrawler\Crawler {
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
}
