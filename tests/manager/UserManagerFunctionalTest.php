<?php

namespace App\Tests\Manager;

use App\Entity\Customer;
use App\Events\UserCreatedEvent;
use App\Managers\CustomerManager;
use Doctrine\ORM\EntityManager;
use PHPUnit_Framework_MockObject_MockObject;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UserManagerFunctionalTest extends WebTestCase
{
    /**
     * Called before doing test.
     *
     * @throws \Exception
     */
    public static function setUpBeforeClass()
    {
        $client = self::createClient();
        $application = new Application($client->getKernel());
        $application->setAutoExit(false);
        $application->run(new StringInput('doctrine:database:drop --env=test --force'));
        $application->run(new StringInput('doctrine:database:create --env=test'));
        $application->run(new StringInput('doctrine:schema:update --env=test --force'));
    }

    /**
     * Test just create user.
     */
    public function testCreateUser(): void
    {
        $client = static::createClient();
        /** @var CustomerManager $service */
        $service = $client->getContainer()->get(CustomerManager::class);
        $entityManager = $client->getContainer()->get('doctrine');
        $user = new Customer();
        $user->setEmail('customer@xyz.com');
        $user->setFirstName('alex');
        $user->setLastName('Canivez');
        $user->setEmail('customer@xyz.com');
        $user->setPassword('toto');
        $service->createCustomer($user);
        $this->assertCount(1, $entityManager->getRepository(Customer::class)->findAll());
    }

    /**
     * Test create user and expect throw event dispatcher.
     */
    public function testCreateUserAndCheckDispatchMessage(): void
    {
        /** @var Client $client */
        $client = static::createClient();

        /**
         * Mock entities.
         */
        /** @var EntityManager&PHPUnit_Framework_MockObject_MockObject $entitymanager */
        $entitymanager = $this->createMock(EntityManager::class);
        /** @var EventDispatcherInterface&PHPUnit_Framework_MockObject_MockObject $dispatcherMock */
        $dispatcherMock = $this->getMockBuilder(EventDispatcher::class)->setMethods(['dispatch'])->getMock();
        /*
         * We tell what we expect
         */
        $dispatcherMock->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->stringContains(UserCreatedEvent::NAME),
                $this->isInstanceOf(Event::class)
            );

        /**
         * Get instance of manager.
         */
        $manager = new CustomerManager($entitymanager, $dispatcherMock);

        /**
         * Create Customer.
         */
        $user = new Customer();
        $user->setEmail('customer@xyz.com');
        $user->setFirstName('alex');
        $user->setLastName('Canivez');
        $user->setEmail('customer@xyz.com');
        $user->setPassword('toto');
        $manager->createCustomer($user);

        $this->assertCount(1, $client->getContainer()->get('doctrine')->getRepository(Customer::class)->findAll());
    }
}
