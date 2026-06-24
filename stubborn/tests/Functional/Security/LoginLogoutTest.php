<?php

namespace App\Tests\Functional\Security;

use App\Tests\Functional\WebTestCaseBase;

class LoginLogoutTest extends WebTestCaseBase
{
    public function test_SEC_01_user_can_login_and_logout()
    {
        // Page login
        $this->visit('/login');

        // Soumission du formulaire
        $form = $this->client->getCrawler()->filter('form')->form([
            '_username' => 'client@test.com',
            '_password' => 'password'
        ]);
        $this->client->submit($form);

        // Redirection vers la page d’accueil
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        // // Vérifie que le menu connecté apparaît
        $this->assertSelectorExists('a[href="/logout"]');

        // // Déconnexion
        $this->client->clickLink($this->t('menu.logout'));

        // // Retour à la page d’accueil
        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        // // Vérifie que le menu visiteur apparaît
        $this->assertSelectorExists('a[href="/login"]');
    }
}
