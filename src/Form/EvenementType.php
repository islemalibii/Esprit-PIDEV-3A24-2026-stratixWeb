<?php
namespace App\Form;

use App\Entity\Evenement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Image;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'attr' => ['placeholder' => 'Nom de l\'événement']
            ])
            ->add('description', TextareaType::class)
            ->add('dateEvent', DateType::class, [
                'widget' => 'single_text', 
                'html5'          => true,
                'property_path'  => 'date_event',
            ])
            ->add('lieu', TextType::class, [
            ])
            ->add('typeEvent', ChoiceType::class, [
                'choices'  => [
                    'Formation'        => 'formation',
                    'Réunion'          => 'reunion',
                    'Lancement Produit'=> 'lancementProduit',
                    'Recrutement'      => 'recrutement',
                    'Seminaire'        => 'seminaire',
                ],
                'expanded' => true, 
                'multiple' => false,
                'property_path'  => 'type_event',
            ])
            ->add('statut', ChoiceType::class, [
                'choices'  => [
                    'Planifié' => 'planifier',
                    'Terminé'  => 'terminer',
                    'Annulé'   => 'annuler',
                ],
                'expanded' => true,  
                'multiple' => false,
                'data' => 'planifier',

            ])

            ->add('image', FileType::class, [
                'required' => false,
                'mapped'   => false,
                'constraints' => [
                    new NotBlank([
                        'message' => "L'image est obligatoire.",
                        'groups'  => ['create'], 
                    ]),
                    new Image([
                        'maxSize' => '5M',
                        'mimeTypesMessage' => "Format invalide. Utilisez JPG, PNG ou WEBP.",
                    ]),
                ],
            ]);
            
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Evenement::class,
        ]);
        
    }
}