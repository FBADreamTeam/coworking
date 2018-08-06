<?php

namespace App\Form;

use App\Entity\Room;
use App\Entity\RoomType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RoomFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('roomType', EntityType::class, array(
                // looks for choices from this entity
                'class' => RoomType::class,
                'label' => 'admin.forms.room.type', //'Type de pièce.',
                // uses the RoomType.label property as the visible option string
                'choice_label' => 'label',
                // used to render a select box, check boxes or radios
                 'multiple' => false,
                 'expanded' => false,
            ))
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'admin.forms.room.name', //'Nom de la pièce',
                'attr' => [
//                    'placeholder' => 'Titre de la pièce...',
                ],
            ])
            ->add('capacity', IntegerType::class, [
                'required' => true,
                'label' => 'admin.forms.room.capacity', //'Capacité de la pièce',
                'attr' => [
//                    'placeholder' => 'Titre de la room...',
                ],
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'admin.forms.room.description', //'Description de la pièce',
                'attr' => [
//                    'placeholder' => 'Description de la pièce...',
                ],
            ])
            ->add('status', TextType::class, [
                'required' => true,
                'label' => 'admin.forms.room.status', //'Status de la pièce',
                'attr' => [
//                    'placeholder' => 'Status de la pièce...',
                ],
            ])
            ->add('hourlyPrice', MoneyType::class, [
                'required' => true,
                'label' => 'admin.forms.room.hourly_price', //'Prix à l\'heure',
                'divisor' => 100,
                'attr' => [
//                    'placeholder' => 'Prix à l\'heure...',
                ],
            ])
            ->add('dailyPrice', MoneyType::class, [
                'required' => true,
                'label' => 'admin.forms.room.daily_price', //'Prix à la journée',
                'divisor' => 100,
                'attr' => [
//                    'placeholder' => 'Prix à la journée...',
                ],
            ])
            ->add('weeklyPrice', MoneyType::class, [
                'required' => true,
                'label' => 'admin.forms.room.weekly_price', //'Prix à la semaine',
                'divisor' => 100,
                'attr' => [
//                    'placeholder' => 'Prix à la semaine...',
                ],
            ])
            ->add('monthlyPrice', MoneyType::class, [
                'required' => true,
                'label' => 'admin.forms.room.monthly_price', //'Prix au mois',
                'divisor' => 100,
                'attr' => [
//                    'placeholder' => 'Prix au mois...',
                ],
            ])
            ->add('featuredImage', FileType::class, [
                'label' => 'admin.forms.room.image', // 'Image'
                'data_class' => null,
                'attr' => [
                    'class' => 'dropify',
                    'data-default-file' => $options['image_url'],
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
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Room::class,
            'image_url' => null,
        ]);
    }
}
