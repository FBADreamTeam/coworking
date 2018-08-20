<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 27/07/2018
 * Time: 15:39.
 */

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CustomerFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    /**
     * EmployeeFixtures constructor.
     *
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $customer = new Customer();
        $customer->setFirstName('Alexandre');
        $customer->setLastName('Canivez');
        $customer->setPassword($this->encoder->encodePassword($customer, 'testtest'));
        $customer->setEmail('alex@test.xyz');
        $address = new Address();
        $address->setStreet('66 rue Test');
        $address->setPostalCode('75019');
        $address->setCity('Paris');
        $address->setCountry('France');
        $customer->addAddress($address);
        $address4 = new Address();
        $address4->setStreet('66 rue Test');
        $address4->setPostalCode('75019');
        $address4->setCity('Paris');
        $address4->setCountry('France');
        $customer->addAddress($address4);

        $customer2 = new Customer();
        $customer2->setFirstName('Fred');
        $customer2->setLastName('Delaval-dupuis');
        $customer2->setPassword($this->encoder->encodePassword($customer, 'testtest'));
        $customer2->setEmail('fred@test.xyz');
        $address2 = new Address();
        $address2->setStreet('66 rue Test');
        $address2->setPostalCode('75019');
        $address2->setCity('Paris');
        $address2->setCountry('France');
        $address2->setAddressCpl('n°2');
        $customer2->addAddress($address2);

        $customer3 = new Customer();
        $customer3->setFirstName('Brahim');
        $customer3->setLastName('Louridi');
        $customer3->setPassword($this->encoder->encodePassword($customer, 'testtest'));
        $customer3->setEmail('brahim@test.xyz');
        $address3 = new Address();
        $address3->setStreet('66 rue Test');
        $address3->setPostalCode('75019');
        $address3->setCity('Paris');
        $address3->setCountry('France');
        $address3->setAddressCpl('n°2');
        $customer2->addAddress($address3);

        $manager->persist($customer);
        $manager->persist($customer2);
        $manager->persist($customer3);

        $manager->flush();
    }
}
