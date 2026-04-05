<?php

namespace App\Form;

use App\Entity\Planning;
use App\Repository\UtilisateurRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanningType extends AbstractType
{
    public function __construct(
        private UtilisateurRepository $utilisateurRepository
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $utilisateurs = $this->utilisateurRepository->findAll();
        $employeChoices = [];
        foreach ($utilisateurs as $u) {
            $employeChoices[$u->getPrenom() . ' ' . $u->getNom()] = $u->getId();
        }

        $builder
            ->add('employe_id', ChoiceType::class, [
                'label'       => 'Employé *',
                'choices'     => $employeChoices,
                'placeholder' => '-- Sélectionner un employé --',
                'attr'        => ['class' => 'form-select'],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'attr'   => ['class' => 'form-control'],
            ])
            ->add('heure_debut', TimeType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime',
                'attr'   => ['class' => 'form-control'],
            ])
            ->add('heure_fin', TimeType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime',
                'attr'   => ['class' => 'form-control'],
            ])
            ->add('type_shift', ChoiceType::class, [
                'choices' => [
                    '☀️ JOUR'       => 'JOUR',
                    '🌆 SOIR'       => 'SOIR',
                    '🌙 NUIT'       => 'NUIT',
                    '🏖️ CONGÉ'     => 'CONGE',
                    '🤒 MALADIE'   => 'MALADIE',
                    '📚 FORMATION' => 'FORMATION',
                    '⚪ AUTRE'     => 'AUTRE',
                ],
                'attr' => ['class' => 'form-select'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Planning::class,
        ]);
    }
}