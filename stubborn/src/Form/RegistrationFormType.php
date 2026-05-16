<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'register.name.label',
                'constraints' => [
                    new Assert\NotBlank(message: 'user.name.not_blank'),
                    new Assert\Length(max: 255, maxMessage: 'user.name.max_length'),
                ],
            ])

            ->add('email', EmailType::class, [
                'label' => 'register.email.label',
                'attr' => ['autocomplete' => 'email'],
                'constraints' => [
                    new Assert\NotBlank(message: 'user.email.not_blank'),
                    new Assert\Email(message: 'user.email.invalid'),
                    new Assert\Length(max: 180, maxMessage: 'user.email.max_length'),
                ],
            ])

            ->add('deliveryAddress', TextType::class, [
                'label' => 'register.delivery.label',
                'constraints' => [
                    new Assert\NotBlank(message: 'user.delivery.not_blank'),
                    new Assert\Length(max: 255, maxMessage: 'user.delivery.max_length'),
                ],
            ])

            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => ['label' => 'register.password.label'],
                'second_options' => ['label' => 'register.password.confirm'],
                'invalid_message' => 'user.password.mismatch',
                'constraints' => [
                    new Assert\NotBlank(message: 'user.password.not_blank'),
                    new Assert\Length(
                        min: 6,
                        minMessage: 'user.password.min_length',
                        max: 4096,
                    ),
                ],
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
