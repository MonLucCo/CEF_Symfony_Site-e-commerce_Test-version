<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ProductService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_admin')]
    public function index(ProductRepository $repo): Response
    {
        return $this->render('admin/index.html.twig', [
            'products' => $repo->findAll(),
            'featuredCount' => $repo->count(['isFeatured' => true]),
        ]);
    }

    #[Route('/product/new', name: 'app_admin_new')]
    public function new(Request $request, ProductService $service): Response
    {
        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->create($product, $form->get('imageFile')->getData());
            $this->addFlash('success', 'admin.flash.product_created');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/product/{id}/edit', name: 'app_admin_edit')]
    public function edit(Product $product, Request $request, ProductService $service): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $service->update($product, $form->get('imageFile')->getData());
            $this->addFlash('success', 'admin.flash.product_updated');
            return $this->redirectToRoute('app_admin');
        }

        return $this->render('admin/edit.html.twig', [
            'form' => $form->createView(),
            'product' => $product,
        ]);
    }

    #[Route('/product/{id}/delete', name: 'app_admin_delete', methods: ['POST'])]
    public function delete(Product $product, Request $request, ProductService $service): Response
    {
        if ($this->isCsrfTokenValid('delete_product_' . $product->getId(), $request->request->get('_token'))) {
            $service->delete($product);
            $this->addFlash('success', 'admin.flash.product_deleted');
        }

        return $this->redirectToRoute('app_admin');
    }

    #[Route('/product/{id}/toggle-featured', name: 'app_admin_toggle_featured')]
    public function toggleFeatured(Product $product, ProductService $service): Response
    {
        if (!$service->toggleFeatured($product)) {
            $this->addFlash('error', 'admin.flash.featured_limit');
        } else {
            $this->addFlash(
                'success',
                $product->isFeatured()
                    ? 'admin.flash.featured_added'
                    : 'admin.flash.featured_removed'
            );
        }

        return $this->redirectToRoute('app_admin');
    }
}
