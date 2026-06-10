<?php

namespace App\Service;

use App\Service\CartService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\User;

class OrderService
{
    private string $defaultlocale = "fr"; // Valeur par défaut, injectée via les paramètres de service

    private CartService $cartService;
    private StripeService $stripeService;
    private EntityManagerInterface $em;
    private MailerInterface $mailer;
    private TranslatorInterface $translator;

    public function __construct(
        CartService $cartService,
        StripeService $stripeService,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        TranslatorInterface $translator,
        string $defaultLocale = "fr"
    ) {
        $this->cartService = $cartService;
        $this->stripeService = $stripeService;
        $this->em = $em;
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->defaultlocale = $defaultLocale;
    }

    /**
     * Vérifie le panier et prépare les données pour Stripe
     */
    public function prepareCheckout(User $user): array
    {
        $items = $this->cartService->getDetailedCart();

        $hasPositiveQuantity = false;
        foreach ($items as $item) {
            if ($item['quantity'] > 0) {
                $hasPositiveQuantity = true;
                break;
            }
        }

        if (!$hasPositiveQuantity) {
            throw new \RuntimeException('empty_cart');
        }

        return $items;
    }

    /**
     * Crée la session Stripe
     */
    public function createStripeSession(array $items)
    {
        return $this->stripeService->createCheckoutSession($items);
    }

    /**
     * Traite la réussite du paiement
     */
    public function processSuccess(User $user): array
    {
        $items = $this->cartService->getDetailedCart();
        $filteredItems = array_filter($items, static function (array $item): bool {
            return $item['quantity'] > 0;
        });

        $total = $this->cartService->getTotal($filteredItems);

        // Mise à jour du stock
        foreach ($filteredItems as $item) {
            $product = $item['product'];
            $size = $item['size'];
            $quantity = $item['quantity'];

            $product->decreaseStockForSize($size, $quantity);
            $this->em->persist($product);
        }
        $this->em->flush();

        // Sauvegarde temporaire en session
        $this->cartService->saveLastOrder($filteredItems, $total);

        // Vider le panier
        $this->cartService->clear();

        return [
            'items' => $filteredItems,
            'total' => $total,
        ];
    }

    /**
     * Envoie l'email de confirmation
     */
    public function sendConfirmationEmail(User $user): void
    {
        $items = $this->cartService->getLastOrderItems();
        $total = $this->cartService->getLastOrderTotal();

        $email = (new TemplatedEmail())
            ->locale($this->defaultlocale) // Utiliser la locale par défaut pour les emails)
            ->from(new Address(
                $this->translator->trans('order_email.from.address', [], 'emails'),
                $this->translator->trans('order_email.from.name', [], 'emails')
            ))
            ->to($user->getEmail())
            ->subject($this->translator->trans('order_email.subject', [], 'emails'))
            ->htmlTemplate('email/order_confirmation.html.twig')
            ->textTemplate('email/order_confirmation.txt.twig')
            ->context([
                'items' => $items,
                'total' => $total,
                'user' => $user,
                'timestamp' => (new \DateTime())->format('Y-m-d H:i:s'),    // Force un nouveau rendu à chaque envoi pour éviter le cache
            ]);

        $this->mailer->send($email);

        // Nettoyage
        $this->cartService->clearLastOrder();
    }
}
