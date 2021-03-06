<?php

namespace App\Form;

use App\Entity\Customer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{
    /**
     * Formulaire d'ajout d'un custumer.
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'First name',
                'attr' => [
                    'placeholder' => 'First name',
                ],
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'label' => 'Last name',
                'attr' => [
                    'placeholder' => 'Last name',
                ],
            ])
            ->add('addresses', CollectionType::class, [
                'entry_type' => AddressType::class,
                'label' => false,
                'entry_options' => array('label' => false),
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email',
                ],
            ])
            ->add('password', PasswordType::class, [
                'required' => ('create' === $options['context']) ? true : false,
                'mapped' => ('edit' === $options['context']) ? false : true,
                'label' => 'Password',
                'attr' => [
                    'placeholder' => 'Password',
                    'minlength' => 8,
                ],
            ])
            ->add('password_confirm', PasswordType::class, [
                'required' => ('create' === $options['context']) ? true : false,
                'mapped' => false, // Permet de spécifier que ce champ n'est pas dans l'entité Employee
                'translation_domain' => 'admin',
                'label' => 'admin.forms.password-confirm',
                'attr' => [
                    'placeholder' => 'admin.forms.pwd-placeholder',
                    'minlength' => 8,
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Valider',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'date_class' => Customer::class,
            'context' => 'create',
        ]);
    }
}
