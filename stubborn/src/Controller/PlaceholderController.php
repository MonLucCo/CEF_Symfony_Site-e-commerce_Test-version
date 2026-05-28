<?php

namespace App\Controller;

use App\Entity\Product;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PlaceholderController extends AbstractController
{
    private function placeholder(string $title): Response
    {
        $this->addFlash('info', $title . ' — placeholder');

        // return $this->render('base.html.twig');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/cart', name: 'app_cart')]
    public function cart(): Response
    {
        return $this->placeholder('Page Panier');
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function addToCart(int $id, Request $request): Response
    {
        $size = $request->query->get('size');

        // Validation défensive : la taille doit être une valeur autorisée
        if (!in_array($size, Product::SIZES, true)) {
            throw $this->createNotFoundException('Taille invalide.');
        }
        return $this->placeholder("Ajout du produit $id au panier (Taille: $size)");
    }
}
