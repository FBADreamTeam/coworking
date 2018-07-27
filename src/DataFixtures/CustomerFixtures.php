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

class CustomerFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $customer = new Customer();
        $customer->setFirstName('first name test 1');
        $customer->setLastName('last name test 1');
        $customer->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $customer->setEmail('test@test.com');

        $customer2 = new Customer();
        $customer2->setFirstName('first name test 2');
        $customer2->setLastName('last name test 2');
        $customer2->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $customer2->setEmail('test2@test.com');

        $manager->persist($customer);
        $manager->persist($customer2);

        $manager->flush();
    }
}