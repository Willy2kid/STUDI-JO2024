<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class AddToCartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('productId', HiddenType::class)
            ->add('solo', SubmitType::class, ['label' => 'Solo', 'attr' => ['data-value' => 'solo']])
            ->add('duo', SubmitType::class, ['label' => 'Duo', 'attr' => ['data-value' => 'duo']])
            ->add('famille', SubmitType::class, ['label' => 'Famille', 'attr' => ['data-value' => 'famille']]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        // Pas de liaison avec une entité
    }
}
