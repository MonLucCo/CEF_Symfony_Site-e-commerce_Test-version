<?php

namespace App\Tests\Fixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppTestFixtures extends Fixture implements FixtureGroupInterface
{
    private const SOURCE = __DIR__ . '/../../public/images/products/';
    private const TARGET = __DIR__ . '/../../var/tests/uploads/';

    private function testFilenameMaker(string $file): string
    {
        $filename =  uniqid('fixture-test_') . '.' . pathinfo($file, PATHINFO_EXTENSION);
        copy(self::SOURCE . $file, self::TARGET . $filename);

        return $filename;
    }

    private function clearTargetDirectory(): void
    {
        $files = glob(self::TARGET . '*'); // Récupère tous les fichiers du dossier cible

        foreach ($files as $file) {
            $basename = basename($file);

            if ($basename[0] === '.') {
                continue; // ignore .gitignore, .DS_Store, etc.
            }

            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public static function getGroups(): array
    {
        return ['test'];
    }

    public function load(ObjectManager $manager): void
    {
        $this->clearTargetDirectory();

        // Utilisateur admin
        $admin = new User();
        $admin->setName('Admin');
        $admin->setEmail('admin@test.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'password'));
        $admin->setDeliveryAddress('Adresse Admin');
        $manager->persist($admin);

        // Utilisateur client (vérifié)
        $user = new User();
        $user->setName('Client');
        $user->setEmail('client@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->setDeliveryAddress('Adresse du client');
        $user->setIsVerified(true);
        $manager->persist($user);

        // Utilisateur client (non-vérifié)
        $user = new User();
        $user->setName('Client nouveau');
        $user->setEmail('nouveau@test.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->setDeliveryAddress('Adresse du client');
        $user->setIsVerified(false);
        $manager->persist($user);

        // Produits
        $numberTestProduct = 5; // Adaptation selon besoin de produits
        $quantityStock = 3; // Adaptation selon besoin du stock
        $featuredCount = 0;
        $maxFeatured = Product::MAX_FEATURED + 0;   // permet de définir le nombre maximum de mise en avant

        for ($i = 1; $i <= $numberTestProduct; $i++) {
            $product = new Product();
            $product->setName("Produit $i");
            $product->setPrice((15 * $i) % 49); // 15€, 30€, 45€ avec une valeur maximale de 48€
            $product->setStockIdemForSize($quantityStock);   // stocks identiques pour les tests

            $imgIndex = ($i % 9) + 1;   // Valeur pour les images dans [1..9]
            $product->setImage($this->testFilenameMaker('product-0' . $imgIndex . '.jpg'));

            if ($featuredCount < $maxFeatured && ($i % 3) === 1) {
                $product->setIsFeatured(true);
                $featuredCount++;
            } else {
                $product->setIsFeatured(false);
            }

            $manager->persist($product);
        }

        $manager->flush();
    }
}
