<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\Utilisateur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('description', TextareaType::class)
            ->add('dateDebut', DateTimeType::class, ['widget' => 'single_text'])
            ->add('dateFin', DateTimeType::class, ['widget' => 'single_text'])
            ->add('budget', NumberType::class)
            ->add('statut', TextType::class, [
                'required' => false,
                'empty_data' => 'Planifié',
            ])
            ->add('responsable', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => fn(Utilisateur $u) => $u->getPrenom() . ' ' . $u->getNom(),
                'placeholder' => 'Choisir un responsable',
            ])
            ->add('membres', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => fn(Utilisateur $u) => $u->getPrenom() . ' ' . $u->getNom(),
                'multiple' => true,
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Projet::class]);
    }
}