<?php

namespace App\Form;

use App\Entity\Tache;
use App\Repository\UtilisateurRepository;
use App\Repository\ProjetRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TacheType extends AbstractType
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository,
        private ProjetRepository $projetRepository
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $utilisateurs = $this->utilisateurRepository->findAll();
        $employeChoices = [];
        foreach ($utilisateurs as $u) {
            $employeChoices[$u->getNom() . ' ' . $u->getPrenom()] = $u->getId();
        }

        $projets = $this->projetRepository->findAll();
        $projetChoices = [];
        foreach ($projets as $p) {
            $projetChoices[$p->getNom()] = $p->getId();
        }

        $builder
            ->add('titre')
            ->add('description')
            ->add('deadline', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'À faire'  => 'A_FAIRE',
                    'En cours' => 'EN_COURS',
                    'Terminée' => 'TERMINEE',
                ],
            ])
            ->add('priorite', ChoiceType::class, [
                'choices' => [
                    'Haute'   => 'HAUTE',
                    'Moyenne' => 'MOYENNE',
                    'Basse'   => 'BASSE',
                ],
            ])
            ->add('employe_id', ChoiceType::class, [
                'label'       => 'Employé',
                'choices'     => $employeChoices,
                'placeholder' => '-- Choisir un employé --',
            ])
            ->add('projet_id', ChoiceType::class, [
                'label'       => 'Projet',
                'choices'     => $projetChoices,
                'placeholder' => '-- Choisir un projet --',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Tache::class,
        ]);
    }
}