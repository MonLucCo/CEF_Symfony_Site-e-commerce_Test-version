<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProductsController extends AbstractController
{
    #[Route('/products', name: 'app_products')]
    public function index(Request $request, ProductRepository $productRepository): Response
    {
        $filter = $request->query->get('price'); // ex: "10-29"

        if ($filter) {
            [$min, $max] = explode('-', $filter);
            $products = $productRepository->findByPriceRange($min, $max);
        } else {
            $products = $productRepository->findAll();
        }

        return $this->render('products/index.html.twig', [
            'products' => $products,
            'filter' => $filter,
        ]);
    }
}
