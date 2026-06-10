<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AccountController extends AbstractController
{
    #[Route('/account/not-verified', name: 'app_account_not_verified')]
    public function notVerified(): Response
    {
        // 1) Si l'utilisateur n'est pas connecté → retour à l'accueil
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        // 2) Si l'utilisateur est déjà vérifié → retour à l'accueil
        if ($this->isGranted('IS_VERIFIED')) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('account/not_verified.html.twig');
    }
}
