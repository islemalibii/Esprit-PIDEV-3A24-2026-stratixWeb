<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\{DateType, FileType, NumberType, TextType, TextareaType, ChoiceType};
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class)
            ->add('description', TextareaType::class, ['required' => false])
            ->add('categorie', ChoiceType::class, [
                'choices'  => [
                    'Électronique' => 'Électronique',
                    'Informatique' => 'Informatique',
                    'Bureau' => 'Bureau',
                    'Autre' => 'Autre',
                ],
            ])
            ->add('prix', NumberType::class)
            ->add('stock_actuel', NumberType::class)
            ->add('stock_min', NumberType::class)
            ->add('ressources_necessaires', TextareaType::class, ['required' => false])
            ->add('date_fabrication', DateType::class, ['widget' => 'single_text', 'required' => false])
            ->add('date_peremption', DateType::class, ['widget' => 'single_text', 'required' => false])
            ->add('date_garantie', DateType::class, ['widget' => 'single_text', 'required' => false])
            // Remplacer ChoisirImage par un champ File
            ->add('image_file', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false, // Ne pas lier directement à l'entité car on doit traiter le fichier
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPG, PNG, GIF)',
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Produit::class]);
    }
}