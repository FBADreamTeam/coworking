<?php

namespace App\Form;

use App\Entity\RoomOption;
use App\Entity\RoomType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomOptionFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('label', TextType::class, [
                    'required' => true,
                    'label' => 'Nom de la pièce',
                    'attr' => [
                    ],
                ])
                ->add('description', TextareaType::class, [
                    'required' => true,
                    'label' => 'Description de l\'option',
                    'attr' => [
                    ],
                ])
                ->add('roomTypes', EntityType::class, array(
                    // looks for choices from this entity
                    'class' => RoomType::class,
                    'label' => 'Type de pièce.',
                    // uses the RoomType.label property as the visible option string
                    'choice_label' => 'label',
                    // used to render a select box, check boxes or radios
                    'multiple' => true,
                    'expanded' => true,
                ))
                ->add('price', MoneyType::class, [
                    'required' => true,
                    'label' => 'Prix de l\'option',
                    'divisor' => 100,
                    'attr' => [
                    ],
                ])
                ->add('Enregistrer', SubmitType::class, array(
                    'attr' => [
                        'class' => 'btn btn-primary',
                    ],
                ));
    }

    /**
     * Définir les options par défaut pour le formulaire.
     *
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'data_class' => RoomOption::class,
            ]);
    }
}
