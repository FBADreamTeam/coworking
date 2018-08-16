<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 26/07/2018
 * Time: 16:55.
 */

namespace App\DataFixtures;

use App\Entity\Employee;
use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class EmployeeFixtures extends Fixture implements DependentFixtureInterface
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
        $roles = $manager->getRepository(Role::class)->findAll();
//
//        foreach ($roles as $key=>$value) {
//            $employee = new Employee();
//            $employee->setFirstName("Test First name".$key);
//            $employee->setLastName("Test Last name".$key);
//            $employee->setEmail("account".$key."@test.com");
//            $employee->setRole($value);
//            $employee->setPassword(password_hash('test', PASSWORD_BCRYPT));
//            $manager->persist($employee);
//        }

        $employees = $this->getEmployees($roles);

        foreach ($employees as $employee) {
            $manager->persist($employee);
        }

        $manager->flush();
    }

    /**
     * @return array
     */
    public function getDependencies()
    {
        return array(
            RoleFixtures::class,
        );
    }

    /**
     * Returns an array of Employees.
     *
     * @param array $roles
     *
     * @return Employee[]
     */
    private function getEmployees(array $roles): array
    {
        $employees = [
            new Employee('Fred', 'Delaval', 'fred@test.xyz', 'testtest', $roles[0]),
            new Employee('Brahim', 'Louridi', 'brahim@test.xyz', 'testtest', $roles[0]),
            new Employee('Alex', 'Canivez', 'alex@test.xyz', 'testtest', $roles[0]),
            new Employee('Employee', 'Test', 'employee@test.xyz', 'testtest', $roles[1]),
        ];

        array_map(function ($employee) {
            /*
             * @var Employee $employee
             */
            $employee->setPassword($this->encoder->encodePassword($employee, $employee->getPassword()));
        }, $employees);

        return $employees;
    }
}
