<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EmailVerifier $emailVerifier,
        private MailerInterface $mailer,
        private \Symfony\Bundle\SecurityBundle\Security $security
    ) {}

    private function sendConfirmationEmail(User $user): bool
    {
        try {
            $email = $this->emailVerifier->buildEmailConfirmation($user, 'app_verify_email');
            $this->mailer->send($email);
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // encode the plain password
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $entityManager->persist($user);
            $entityManager->flush();

            // Préparation de l'email avec URL signée
            $email = $this->emailVerifier->buildEmailConfirmation($user, 'app_verify_email');

            // Envoi de l'email + gestion des messages
            if ($this->sendConfirmationEmail($user)) {
                $this->addFlash('success', 'register.email.sent');
            } else {
                $this->addFlash('error', 'register.email.error');
            }

            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(
        Request $request,
    ): Response {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        try {
            // Tentative de validation du lien
            $isNewVerification = $this->emailVerifier->handleEmailConfirmation($request, $user);

            if ($isNewVerification) {
                $this->addFlash('success', 'verify.success');
            } else {
                $this->addFlash('info', 'verify.already_verified');
            }

            return $this->redirectToRoute('app_home');
        } catch (VerifyEmailExceptionInterface $e) {
            // On considère le lien comme compromis / invalide / expiré
            $this->addFlash('error', 'verify.error');

            return $this->redirectToRoute('app_home');
        }
    }

    #[Route('/register/resend', name: 'app_register_resend')]
    public function resendVerificationEmail(): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        /** @var User $user */
        $user = $this->getUser();

        if ($user->isVerified()) {
            $this->addFlash('info', 'verify.already_verified');
            return $this->redirectToRoute('app_home');
        }

        if ($this->sendConfirmationEmail($user)) {
            $this->addFlash('success', 'register.email.resent');
        } else {
            $this->addFlash('error', 'register.email.error');
        }

        return $this->redirectToRoute('app_home');
    }
}
