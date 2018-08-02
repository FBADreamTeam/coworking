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
                'required' => ($options['context'] == 'create') ? true : false,
                'mapped' => ($options['context'] == 'edit') ? false : true,
                'translation_domain' => 'admin',
                'label' => 'admin.forms.password',
                'attr' => [
                    'placeholder' => 'admin.forms.pwd-placeholder',
                ]
            ])
            ->add('password_confirm', PasswordType::class, [
                'required' => ($options['context'] == 'create') ? true : false,
                'mapped' => false, // Permet de spécifier que ce champ n'est pas dans l'entité Employee
                'translation_domain' => 'admin',
                'label' => 'admin.forms.password-confirm',
                'attr' => [
                    'placeholder' => 'admin.forms.pwd-placeholder',
                ]
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'admin.forms.btn.submit',
                'translation_domain' => 'admin',
                'attr' => ['class' => 'btn btn-primary'],
            ]);

        if ( $options['role'] === 'ROLE_ADMIN') {
            $builder->add('role', EntityType::class, [
                'class' => Role::class,
                'choice_label'   => 'label',
                'required' => true,
                'translation_domain' => 'admin',
                'label' => 'admin.forms.role',
                'attr' => [
                    'placeholder' => 'admin.forms.role',
                ],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'date_class' => Employee::class,
            'role' => 'ROLE_EMPLOYEE',
            'context' => 'create'
        ]);
    }
}