<?php

namespace App\Form;

use App\Entity\Address;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', TextType::class, [
                'translation_domain' => 'booking',
                'label' => 'booking.titles.street'
            ])
            ->add('postalCode', IntegerType::class, [
                'translation_domain' => 'booking',
                'label' => 'booking.titles.zipcode',
                'attr' => [
                    'maxlength' => 5
                ]
            ])
            ->add('city', TextType::class, [
                'translation_domain' => 'booking',
                'label' => 'booking.titles.city'
            ])
            ->add('country', TextType::class, [
                'translation_domain' => 'booking',
                'label' => 'booking.titles.country'
            ])
            ->add('addressCpl', TextType::class, [
                'translation_domain' => 'booking',
                'label' => 'booking.titles.addressCpl',
                'required' => false
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
