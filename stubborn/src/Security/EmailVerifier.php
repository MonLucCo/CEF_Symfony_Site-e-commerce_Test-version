<?php

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\Request;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    public function __construct(
        private VerifyEmailHelperInterface $verifyEmailHelper,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Prépare l'email de confirmation (ajoute l'URL signée + expiration).
     */
    private function prepareEmailConfirmation(string $routeName, User $user, TemplatedEmail $email): TemplatedEmail
    {
        $signature = $this->verifyEmailHelper->generateSignature(
            $routeName,
            $user->getId(),
            $user->getEmail()
        );

        return $email->context([
            'signedUrl' => $signature->getSignedUrl(),
            'expiresAt' => $signature->getExpiresAt(),
        ]);
    }

    /**
     * Produit l'email de confirmation.
     */
    public function buildEmailConfirmation(User $user, string $routeName): TemplatedEmail
    {
        return $this->prepareEmailConfirmation(
            $routeName,
            $user,
            (new TemplatedEmail())
                ->from(new Address('no-reply@stubborn.com', 'Stubborn Mailer'))
                ->to($user->getEmail())
                ->subject('register.email.subject')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );
    }

    /**
     * Valide le lien et met à jour isVerified.
     */
    public function handleEmailConfirmation(Request $request, User $user): bool
    {
        if ($user->isVerified()) {
            return false; // Déjà vérifié, pas besoin de faire quoi que ce soit
        }
        $this->verifyEmailHelper->validateEmailConfirmationFromRequest(
            $request,
            $user->getId(),
            $user->getEmail()
        );

        // 🔒 Empêcher la vérification d'un administrateur
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return ! ($user->isVerified());
        }

        // réaliser la vérification
        $user->setIsVerified(true);
        $this->entityManager->flush();

        return $user->isVerified();
    }
}
