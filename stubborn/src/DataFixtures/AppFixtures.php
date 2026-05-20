<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Product;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        // --- USERS ---

        // Admin
        $admin = new User();
        $admin->setName('Admin');
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setIsVerified(false);       // Un Admin ne doit jamais être vérifié par email. Il ne pourra pas accéder aux achats.
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Client
        $client = new User();
        $client->setName('Client Test-1');
        $client->setEmail('client1@example.com');
        $client->setRoles(['ROLE_USER']);
        $client->setIsVerified(true);
        $client->setPassword($this->passwordHasher->hashPassword($client, 'client1'));
        $client->setDeliveryAddress('1 rue des Tests, 75000 Paris');
        $manager->persist($client);

        $client = new User();
        $client->setName('Client Test-2');
        $client->setEmail('client2@example.com');
        $client->setRoles(['ROLE_USER']);
        $client->setIsVerified(false);
        $client->setPassword($this->passwordHasher->hashPassword($client, 'client2'));
        $client->setDeliveryAddress('2 rue des Tests, 75000 Paris');
        $manager->persist($client);

        // --- PRODUCTS ---

        $products = [
            ['Blackbelt', 'product-01.jpg', 29.90, true],
            ['BlueBelt.', 'product-02.jpg', 29.90, false],
            ['Street.', 'product-03.jpg', 34.50, false],
            ['Pokeball', 'product-04.jpg', 45.00, true],
            ['PinkLady', 'product-05.jpg', 29.90, false],
            ['Snow', 'product-06.jpg', 32.00, false],
            ['Greyback', 'product-07.jpg', 28.50, false],
            ['BlueCloud', 'product-08.jpg', 45.00, false],
            ['BornInUsa', 'product-09.jpg', 59.90, true],
            ['GreenSchool', 'product-10.jpg', 42.20, false],
        ];

        foreach ($products as [$name, $image, $price, $featured]) {
            $product = new Product();
            $product->setName($name);
            $product->setImage($image);
            $product->setPrice($price);
            $product->setIsFeatured($featured);

            // stocks par défaut
            $product->setStockXS(10);
            $product->setStockS(10);
            $product->setStockM(10);
            $product->setStockL(10);
            $product->setStockXL(10);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
