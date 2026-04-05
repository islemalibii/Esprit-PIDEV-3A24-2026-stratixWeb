<?php

namespace App\Form;

use App\Entity\Ressource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RessourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('type_ressource', ChoiceType::class, [
                'choices'  => [
                    'Matériel informatique' => 'Matériel informatique',
                    'Mobilier' => 'Mobilier',
                    'Fourniture de bureau' => 'Fourniture de bureau',
                    'Équipement' => 'Équipement',
                    'Matière première' => 'Matière première',
                    'Logiciel' => 'Logiciel',
                    'Autre' => 'Autre',
                ],
                'attr' => ['id' => 'type-select']
            ])
            ->add('quantite', IntegerType::class)
            ->add('fournisseur', TextType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Ressource::class,
        ]);
    }
}