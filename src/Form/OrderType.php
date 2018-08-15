<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Order;
use App\Repository\AddressRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('address', EntityType::class, [
//                'class' => Address::class,
//                'expanded' => true,
//                'multiple' => false,
//                'label' => false,
//                'query_builder' => function(EntityRepository $er) {
//                    return $er->createQueryBuilder('a');
//                },
//            ])
            ->add('submit', SubmitType::class, [
                'translation_domain' => 'booking',
                'label' => 'booking.btn.submit',
            ])
            ->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
                /** @var Order $order */
                $order = $event->getData();
                $form = $event->getForm();
                $form->add('address', EntityType::class, [
                    'class' => Address::class,
                    'expanded' => true,
                    'multiple' => false,
                    'label' => false,
                    'query_builder' => function (AddressRepository $ar) use ($order) {
                        return $ar->getAddressesFromCustomer($order->getBooking()->getCustomer());
                    },
                ]);
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
