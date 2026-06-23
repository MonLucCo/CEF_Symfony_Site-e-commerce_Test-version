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

    private const SOURCE = __DIR__ . '/../../public/images/products/';
    private const TARGET = __DIR__ . '/../../public/uploads/products/';

    // Les données des produitsà insérer en base de données
    // Le format de chaque produit est : [name, image, price, isFeatured, stockQuantity]
    private const PRODUCTS = [
        ['Blackbelt', 'product-01.jpg', 29.90, true, 10],
        ['BlueBelt', 'product-02.jpg', 29.90, false, 10],
        ['Street.', 'product-03.jpg', 34.50, false, 10],
        ['Pokeball', 'product-04.jpg', 45.00, true, 10],
        ['PinkLady', 'product-05.jpg', 29.90, false, 10],
        ['Snow', 'product-06.jpg', 32.00, false, 10],
        ['Greyback', 'product-07.jpg', 28.50, false, 10],
        ['BlueCloud', 'product-08.jpg', 45.00, false, 10],
        ['BornInUsa', 'product-09.jpg', 59.90, true, 10],
        ['GreenSchool', 'product-10.jpg', 42.20, false, 10],
    ];

    // Les données des utilisateurs à insérer en base de données
    // Le format de chaque utilisateur est : [name, email, password, roles, isVerified, deliveryAddress]
    // Note : 
    // - les mots de passe seront hashés avant d'être insérés en base de données, grâce au service 
    //   UserPasswordHasherInterface injecté dans le constructeur de cette classe.
    // - les rôles sont à indiquer sous forme de tableau, même s'il n'y en a qu'un seul. Par exemple : ['ROLE_ADMIN'] 
    //   ou ['ROLE_USER']
    // - le champ isVerified indique si l'utilisateur a vérifié son adresse email ou pas. Un utilisateur non vérifié 
    //   ne pourra pas accéder à la page d'achat.
    // - un administrateur (ROLE_ADMIN) ne doit jamais être vérifié, même si c'est contre-intuitif. Cela permet de 
    //   s'assurer que les administrateurs ne pourront jamais accéder à la page d'achat, même s'ils ont un compte.
    private const USERS = [
        ['Admin', 'admin@example.com', 'admin123', ['ROLE_ADMIN'], false, '1 rue des Admins, 75000 Paris'],
        ['Client Test-1', 'client1@example.com', 'client1', ['ROLE_USER'], true, '1 rue des Tests, 75000 Paris'],
        ['Client Test-2', 'client2@example.com', 'client2', ['ROLE_USER'], false, '2 rue des Tests, 75000 Paris'],
    ];

    private function filenameMaker(string $file): string
    {
        $filename =  uniqid('fixture_') . '.' . pathinfo($file, PATHINFO_EXTENSION);
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

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->clearTargetDirectory();

        // --- USERS ---

        foreach (self::USERS as [$name, $email, $password, $roles, $isVerified, $deliveryAddress]) {
            $user = new User();
            $user->setName($name);
            $user->setEmail($email);
            $user->setRoles($roles);
            $user->setIsVerified($isVerified);
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $user->setDeliveryAddress($deliveryAddress);

            $manager->persist($user);
        }

        // --- PRODUCTS ---


        foreach (self::PRODUCTS as [$name, $image, $price, $featured, $stockQuantity]) {
            $product = new Product();
            $product->setName($name);
            $product->setImage($this->filenameMaker($image));
            $product->setPrice($price);
            $product->setIsFeatured($featured);

            // stocks par défaut
            foreach (Product::SIZES as $size) {
                $setter = 'setStock' . $size;
                $product->$setter($stockQuantity);
            }

            $manager->persist($product);
        }

        $manager->flush();
    }
}
