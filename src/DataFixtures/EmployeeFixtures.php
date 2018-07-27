<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 26/07/2018
 * Time: 16:55
 */

namespace App\DataFixtures;


use App\Entity\Employee;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EmployeeFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $roles = $manager->getRepository(Role::class)->findAll();

        foreach ($roles as $key=>$value) {
            $employee = new Employee();
            $employee->setFirstName("Test First name".$key);
            $employee->setLastName("Test Last name".$key);
            $employee->setEmail("account".$key."@test.com");
            $employee->setRole($value);
            $employee->setPassword(password_hash('test', PASSWORD_BCRYPT));
            $manager->persist($employee);
        }

        $manager->flush();
    }

    public function getDependencies()
    {
        return array(
            RoleFixtures::class
        );
    }

}