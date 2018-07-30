<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 27/07/2018
 * Time: 11:38
 */

namespace App\Form;

use App\Entity\Employee;
use App\Entity\Role;
use App\Traits\TranslatorTrait;
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
                'translation_domain' => 'admin',
                'label' => 'admin.forms.firstname',
                'attr'  => [
                    'placeholder' => 'admin.forms.firstname',
                ]
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'translation_domain' => 'admin',
                'label' => 'admin.forms.lastname',
                'attr' => [
                    'placeholder' => 'admin.forms.lastname',
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => true,
                'translation_domain' => 'admin',
                'label' => 'admin.forms.email',
                'attr' => [
                    'placeholder' => 'admin.forms.email',
                ]
            ])
            ->add('password', PasswordType::class, [
                'required' => true,
                'translation_domain' => 'admin',
                'label' => 'admin.forms.password',
                'attr' => [
                    'placeholder' => 'admin.forms.pwd-placeholder',
                ]
            ])
            ->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label'   => 'label',
                'required' => true,
                'translation_domain' => 'admin',
                'label' => 'admin.forms.role',
                'attr' => [
                    'placeholder' => 'admin.forms.role',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'admin.forms.btn.submit',
                'translation_domain' => 'admin',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'date_class' => Employee::class
        ]);
    }
}