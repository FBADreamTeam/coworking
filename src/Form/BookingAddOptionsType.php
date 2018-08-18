<?php

namespace App\Form;

use App\Entity\Booking;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingAddOptionsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('bookingOptions', CollectionType::class, [
                // each entry in the array will be a bookingOption field
                'entry_type' => BookingOptionsType::class,
            ])
            ->add('submit', SubmitType::class, [
                'translation_domain' => 'booking',
                'label' => 'booking.btn.book',
                'attr' => ['class' => 'btn-primary w-100 font-weight-bold'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Booking::class,
        ]);
    }
}
