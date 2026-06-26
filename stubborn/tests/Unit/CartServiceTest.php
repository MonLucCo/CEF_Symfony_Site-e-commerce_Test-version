<?php

namespace App\Tests\Unit;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\CartService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CartServiceTest extends TestCase
{
    private CartService $cartService;
    private Session $session;
    private ProductRepository $productRepository;

    protected function setUp(): void
    {
        // Session en mémoire
        $this->session = new Session(new MockArraySessionStorage());
        $this->session->start();

        // RequestStack avec une Request qui porte la session
        $request = new Request();
        $request->setSession($this->session);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        // Repository mocké
        $this->productRepository = $this->createMock(ProductRepository::class);

        // Service réel
        $this->cartService = new CartService($requestStack, $this->productRepository);
    }

    private function createProduct(float $price = 20.0, int $stock = 3): Product
    {
        $product = new Product();
        $product->setName('Test product');
        $product->setPrice(number_format($price, 2, '.', ''));
        $product->setImage('test.jpg');
        $product->setStockIdemForSize($stock);

        return $product;
    }

    public function test_CRT_U01_add_product_to_cart_increases_quantity()
    {
        $product = $this->createProduct(price: 20, stock: 3);

        $this->productRepository
            ->method('find')
            ->with(1)
            ->willReturn($product);

        // Panier initial vide
        $this->assertSame([], $this->session->get('cart', []));

        // Ajout 1 fois
        $this->cartService->add(1, 'M');

        $cart = $this->session->get('cart', []);
        $this->assertArrayHasKey(1, $cart);
        $this->assertArrayHasKey('M', $cart[1]);
        $this->assertSame(1, $cart[1]['M']['quantity']);

        // Ajout une deuxième fois
        $this->cartService->add(1, 'M');

        $cart = $this->session->get('cart', []);
        $this->assertSame(2, $cart[1]['M']['quantity']);
    }

    public function test_CRT_U02_add_respects_stock_limit()
    {
        $product = $this->createProduct(price: 20, stock: 2);

        $this->productRepository
            ->method('find')
            ->with(1)
            ->willReturn($product);

        // Ajout 3 fois alors que stock = 2
        $this->cartService->add(1, 'M');
        $this->cartService->add(1, 'M');
        $this->cartService->add(1, 'M');

        $cart = $this->session->get('cart', []);
        $this->assertSame(2, $cart[1]['M']['quantity']);
    }

    public function test_CRT_U03_decrease_decrements_quantity_but_not_below_zero()
    {
        $product = $this->createProduct();

        $this->productRepository
            ->method('find')
            ->willReturn($product);

        // Prépare un panier avec quantité 1
        $this->cartService->add(1, 'M');
        $cart = $this->session->get('cart', []);
        $this->assertSame(1, $cart[1]['M']['quantity']);

        // decrease → 0
        $this->cartService->decrease(1, 'M');
        $cart = $this->session->get('cart', []);
        $this->assertSame(0, $cart[1]['M']['quantity']);

        // decrease encore → reste 0
        $this->cartService->decrease(1, 'M');
        $cart = $this->session->get('cart', []);
        $this->assertSame(0, $cart[1]['M']['quantity']);
    }

    public function test_CRT_U04_remove_removes_size_and_product_when_last_size()
    {
        $product = $this->createProduct();

        $this->productRepository
            ->method('find')
            ->willReturn($product);

        // Ajout sur deux tailles
        $this->cartService->add(1, 'M');
        $this->cartService->add(1, 'L');

        $cart = $this->session->get('cart', []);
        $this->assertArrayHasKey('M', $cart[1]);
        $this->assertArrayHasKey('L', $cart[1]);

        // remove taille M
        $this->cartService->remove(1, 'M');
        $cart = $this->session->get('cart', []);
        $this->assertArrayNotHasKey('M', $cart[1]);
        $this->assertArrayHasKey('L', $cart[1]);

        // remove taille L → plus aucune taille → produit supprimé
        $this->cartService->remove(1, 'L');
        $cart = $this->session->get('cart', []);
        $this->assertArrayNotHasKey(1, $cart);
    }

    public function test_CRT_U05_clear_removes_cart_from_session()
    {
        $product = $this->createProduct();

        $this->productRepository
            ->method('find')
            ->willReturn($product);

        $this->cartService->add(1, 'M');
        $this->assertNotEmpty($this->session->get('cart', []));

        $this->cartService->clear();
        $this->assertSame([], $this->session->get('cart', []));
    }

    public function test_CRT_U06_get_detailed_cart_and_total()
    {
        $product1 = $this->createProduct(price: 10, stock: 5);
        $product2 = $this->createProduct(price: 20, stock: 5);

        $this->productRepository
            ->method('find')
            ->willReturnMap([
                [1, $product1],
                [2, $product2],
            ]);

        // Prépare le panier
        $this->cartService->add(1, 'M'); // 10
        $this->cartService->add(1, 'M'); // 20
        $this->cartService->add(2, 'L'); // 20

        $items = $this->cartService->getDetailedCart();
        $this->assertCount(2, $items);

        // total attendu = 10*2 + 20*1 = 40
        $total = $this->cartService->getTotal($items);
        $this->assertEquals(40.0, $total);
    }
}
