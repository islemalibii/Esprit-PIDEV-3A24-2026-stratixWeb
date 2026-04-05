<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $isEdit = $options['is_edit'];

        $builder
            ->add('nom', TextType::class, ['label' => 'Nom'])
            ->add('prenom', TextType::class, ['label' => 'Prénom'])
            ->add('cin', IntegerType::class, ['label' => 'CIN'])
            ->add('email', EmailType::class, ['label' => 'Email', 'required' => false])
            ->add('tel', TextType::class, ['label' => 'Téléphone', 'required' => false])
            ->add('role', ChoiceType::class, [
                'label'   => 'Rôle',
                'choices' => [
                    'Administrateur'          => 'admin',
                    'Employé'                 => 'employe',
                    'Responsable RH'          => 'responsable_rh',
                    'Responsable Projet'      => 'responsable_projet',
                    'Responsable Production'  => 'responsable_production',
                    'CEO'                     => 'ceo',
                ],
            ])
            ->add('statut', ChoiceType::class, [
                'label'    => 'Statut',
                'required' => false,
                'choices'  => [
                    'Actif'   => 'actif',
                    'Inactif' => 'inactif',
                ],
                'placeholder' => 'Choisir...',
            ])
            ->add('department', TextType::class, ['label' => 'Département', 'required' => false])
            ->add('poste', TextType::class, ['label' => 'Poste', 'required' => false])
            ->add('date_embauche', DateType::class, [
                'label'    => 'Date d\'embauche',
                'widget'   => 'single_text',
                'required' => false,
            ])
            ->add('competences', TextareaType::class, ['label' => 'Compétences', 'required' => false])
            ->add('salaire', NumberType::class, ['label' => 'Salaire', 'required' => false])
            ->add('plainPassword', PasswordType::class, [
                'label'    => 'Mot de passe',
                'mapped'   => false,
                'required' => !$isEdit,
                'constraints' => $isEdit ? [] : [
                    new NotBlank(['message' => 'Le mot de passe est requis.']),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Minimum {{ limit }} caractères.',
                        'max' => 64,
                    ]),
                    new Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'Le mot de passe doit contenir au moins une majuscule.',
                    ]),
                    new Regex([
                        'pattern' => '/[0-9]/',
                        'message' => 'Le mot de passe doit contenir au moins un chiffre.',
                    ]),
                ],
                'attr' => ['placeholder' => $isEdit ? 'Laisser vide pour ne pas changer' : 'Min. 8 caractères, 1 majuscule, 1 chiffre'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
            'is_edit'    => false,
        ]);
    }
}
