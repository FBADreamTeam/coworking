<?php

namespace App\Tests\Manager;

use App\Entity\Customer;
use App\Managers\CustomerManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use PHPUnit_Framework_MockObject_MockObject;

class UserManagerTest extends TestCase
{
    public function testCanBeUsed(): void
    {
        // UserManager dependencies

        /** @var EntityManagerInterface&PHPUnit_Framework_MockObject_MockObject $objectManager */
        $objectManager = $this->createMock(EntityManagerInterface::class);
        $objectManager->expects($this->once())->method('persist');
        $objectManager->expects($this->once())->method('flush');
        /** @var UserPasswordEncoderInterface&PHPUnit_Framework_MockObject_MockObject $encoder */
        $encoder = $this->createConfiguredMock(UserPasswordEncoderInterface::class, [
            'encodePassword' => '&djhhte889402JJFUVFFZFZF4',
        ]);
        /** @var EventDispatcherInterface&PHPUnit_Framework_MockObject_MockObject $dispatcher */
        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $user = new Customer();

        $user->setPassword($encoder->encodePassword($user, 'toto'));
        //	    $manager = new CustomerManager($objectManager, $encoder);
        $manager = new CustomerManager($objectManager, $dispatcher);
        $manager->createCustomer($user);

        $this->assertSame('&djhhte889402JJFUVFFZFZF4', $user->getPassword());
    }
}
