<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 27/07/2018
 * Time: 15:39
 */

namespace App\DataFixtures;

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
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $customer = new Customer();
        $customer->setFirstName('first name test 1');
        $customer->setLastName('last name test 1');
        $customer->setPassword($this->encoder->encodePassword($customer, 'testtest'));
        $customer->setEmail('test@test.xyz');

        $customer2 = new Customer();
        $customer2->setFirstName('first name test 2');
        $customer2->setLastName('last name test 2');
        $customer2->setPassword($this->encoder->encodePassword($customer, 'testtest'));
        $customer2->setEmail('test2@test.xyz');

        $manager->persist($customer);
        $manager->persist($customer2);

        $manager->flush();
    }
}
