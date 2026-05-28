<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/products', name: 'app_products')]
    public function products(): Response
    {
        return $this->placeholder('Page Produits');
    }

    #[Route('/cart', name: 'app_cart')]
    public function cart(): Response
    {
        return $this->placeholder('Page Panier');
    }

    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(int $id): Response
    {
        return $this->placeholder("Page produit $id");
    }
}
