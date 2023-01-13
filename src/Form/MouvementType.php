<?php

namespace App\Form;

use App\Entity\Mouvement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MouvementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mouv_type', TextType::class,['label' => 'Type'])
            ->add('mouv_quantite', NumberType::class,['label' => 'Quantite'])
            ->add('mouv_date', DateType::class,['widget' => 'single_text'])
            ->add('produit')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Mouvement::class,
        ]);
    }
}
