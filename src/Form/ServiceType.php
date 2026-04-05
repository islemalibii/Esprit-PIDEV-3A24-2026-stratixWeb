<?php

namespace App\Form;

use App\Entity\CategorieService;
use App\Entity\Utilisateur;
use App\Repository\CategorieServiceRepository;
use App\Repository\UtilisateurRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ServiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Titre du service',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le titre est obligatoire']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'constraints' => [
                    new Assert\Length([
                        'max' => 1000,
                        'maxMessage' => 'La description ne peut pas dépasser {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('dateDebut', DateType::class, [
                'label' => 'Date de début',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de début est obligatoire']),
                    new Assert\GreaterThanOrEqual([
                        'value' => 'today',
                        'message' => 'La date de début ne peut pas être dans le passé'
                    ])
                ]
            ])
            ->add('dateFin', DateType::class, [
                'label' => 'Date de fin',
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La date de fin est obligatoire'])
                ]
            ])
            ->add('budget', MoneyType::class, [
                'label' => 'Budget (DT)',
                'currency' => 'TND',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le budget est obligatoire']),
                    new Assert\Positive(['message' => 'Le budget doit être positif'])
                ]
            ])
            ->add('categorie', EntityType::class, [
                'class' => CategorieService::class,
                'choice_label' => 'nom',
                'label' => 'Catégorie',
                'placeholder' => 'Sélectionner une catégorie',
                'query_builder' => function (CategorieServiceRepository $repo) {
                    return $repo->createQueryBuilder('c')
                        ->where('c.archive = false')
                        ->orderBy('c.nom', 'ASC');
                },
                'constraints' => [
                    new Assert\NotNull(['message' => 'La catégorie est obligatoire'])
                ]
            ])
            ->add('utilisateur', EntityType::class, [
                'class' => Utilisateur::class,
                'choice_label' => function(Utilisateur $user) {
                    return $user->getPrenom() . ' ' . $user->getNom();
                },
                'label' => 'Responsable',
                'placeholder' => 'Sélectionner un responsable',
                'required' => false,
                'query_builder' => function (UtilisateurRepository $repo) {
                    return $repo->createQueryBuilder('u')
                        ->orderBy('u.nom', 'ASC')
                        ->addOrderBy('u.prenom', 'ASC');
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => \App\Entity\Service::class,
        ]);
    }
}
