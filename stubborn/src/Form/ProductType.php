<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /**
         * Champ : name
         * -------------------------
         * Nom du produit.
         * Obligatoire selon le sujet du CEF.
         */
        $builder->add('name', TextType::class, [
            'label' => 'product.form.name.label',
            'required' => true,
            'help' => 'product.form.name.help',
            'attr' => ['maxlength' => 255],
        ]);

        /**
         * Champ : price
         * -------------------------
         * MoneyType permet de gérer automatiquement la devise et le format.
         * On impose un prix strictement positif car :
         *  - aucun produit n'est gratuit
         *  - le sujet du CEF ne prévoit pas de prix nul
         *  - cela évite les erreurs métier
         */
        $builder->add('price', MoneyType::class, [
            'label' => 'product.form.price.label',
            'currency' => 'EUR',
            'required' => true,
            'help' => 'product.form.price.help',
            'scale' => 2,
            'attr' => ['min' => 0.01],
        ]);

        /**
         * Champ : imageFile
         * -------------------------
         * Pourquoi pas 'image' ?
         *  - 'image' est un champ string en base (nom du fichier)
         *  - FileType manipule un UploadedFile (objet)
         *  - on ne peut pas mapper un fichier sur une colonne string
         *
         * Donc :
         *  - imageFile est un champ technique NON MAPPÉ
         *  - le contrôleur gère l'upload et met à jour $product->setImage()
         */
        $builder->add('imageFile', FileType::class, [
            'label' => 'product.form.image.label',
            'mapped' => false,
            'required' => false,
            'help' => 'product.form.image.help',
            'constraints' => [
                new File(
                    maxSize: '2M',
                    mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                )
            ],
        ]);

        /**
         * Champs : stockXS / stockS / stockM / stockL / stockXL
         * -----------------------------------------------------
         * Pourquoi required => false ?
         *  - le sujet du CEF n'impose pas de stock minimal
         *  - un admin peut créer un produit sans stock initial
         *  - l'entité définit déjà une valeur par défaut = 0
         *
         * empty_data => '0' garantit que si le champ est vide,
         * Symfony hydrate l'entité avec 0.
         */
        foreach (Product::SIZES as $size) {
            $builder->add('stock' . $size, IntegerType::class, [
                'label' => 'product.form.stock.' . strtolower($size),
                'required' => false,
                'empty_data' => '0',
                'attr' => ['min' => 0],
            ]);
        }

        /**
         * IMPORTANT :
         * Le champ 'isFeatured' n'est PAS dans ce formulaire.
         *
         * Pourquoi ?
         *  - La mise en avant est une action métier indépendante
         *  - Elle dépend de l'état global (max 3 produits)
         *  - Le wireframe montre une action par ligne, pas dans le formulaire
         *  - Le formulaire de création doit rester minimal
         *
         * La mise en avant sera gérée dans AdminController via un bouton dédié.
         */
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        /**
         * data_class indique à Symfony que ce formulaire hydrate un Product.
         * C'est indispensable pour que handleRequest() fonctionne.
         * translation_domain permet d'utiliser des clés de traduction spécifiques aux formulaires.
         */
        $resolver->setDefaults([
            'data_class' => Product::class,
            'translation_domain' => 'forms', // i18n propre
        ]);
    }
}
