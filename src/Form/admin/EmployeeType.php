<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 27/07/2018
 * Time: 11:38
 */

namespace App\Form\admin;


use App\Entity\Employee;
use App\Entity\Role;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'First name',
                'attr'  => [
                    'placeholder' => 'First name'
                ]
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'label' => 'Last name',
                'attr' => [
                    'placeholder' => 'Last name'
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Email'
                ]
            ])
            ->add('password', PasswordType::class, [
                'required' => true,
                'label' => 'Password',
                'attr' => [
                    'placeholder' => 'Password'
                ]
            ])
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label'   => 'label',
                'required' => true,
                'label' => "Fonction",
                'attr' => [
                    'placeholder' => "Fonction"
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Valider'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'date_class' => Employee::class
        ]);
    }
}