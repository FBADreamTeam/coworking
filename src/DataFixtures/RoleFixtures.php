<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 26/07/2018
 * Time: 16:29
 */

namespace App\DataFixtures;


use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class RoleFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $role = new Role('ROLE_ADMIN');
        $role2 = new Role('ROLE_EMPLOYEE');

        $manager->persist($role);
        $manager->persist($role2);

        $manager->flush();
    }
}