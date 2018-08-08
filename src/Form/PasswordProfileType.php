<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PasswordProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['context'] == 'create') {
            $builder
                ->add('email', EmailType::class, [
                ]);
        }

        if ($options['context'] == 'edit') {
            $builder
                ->add('password', PasswordType::class, [
                    'label' => 'Saisissez votre nouveau mot de passe',
                    'attr' => [
                        'minlength' => 8
                    ]
                ])
                ->add('password_confirm', PasswordType::class, [
                    'label' => 'Saisissez une nouvelle foi votre mot de passe',
                    'attr' => [
                        'minlength' => 8
                    ]
                ]);
        }

        $builder->add('submit', SubmitType::class, [
            'label' => 'Envoyer'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'context' => ''
        ]);
    }
}