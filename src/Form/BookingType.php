<?php

namespace App\Form;

use App\Entity\Booking;
use App\Entity\Customer;
use App\Entity\Room;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BookingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startDate', DateType::class, [
                'translation_domain'    => 'admin',
                'label'                 => 'admin.forms.booking.start_date'
            ])
            ->add('endDate', DateType::class, [
                'translation_domain'    => 'admin',
                'label'                 => 'admin.forms.booking.end_date'
            ])
            ->add('room', EntityType::class, [
                'class'         => Room::class,
                'choice_label'  => 'name',
                'query_builder' => function (EntityRepository $repo) use($options) {
                    return $repo
                        ->createQueryBuilder('r')
                        ->innerJoin('roomType', 'rt')
                        ->where('rt.name = :name')
                        ->setParameter('label',$options['room_category'])
                    ;
                },
            ])
            ->add('customer', EntityType::class, [
                'class'                 => Customer::class,
                'choice_label'          => 'fullName',
                'translation_domain'    => 'admin',
                'label'                 => 'admin.forms.booking.customer',
            ])
            ->add('submit', SubmitType::class, [
                'translation_domain'    => 'admin',
                'label'                 => 'admin.forms.btn.submit',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'    => Booking::class,
            'room_category' => 'Bureau',
        ]);
    }
}
