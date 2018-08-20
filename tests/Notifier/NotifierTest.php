<?php

namespace App\Tests\Notifier;

use App\Entity\Customer;
use App\Events\UserEvents;
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

class NotifierTest extends WebTestCase
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
        $application->run(new StringInput('doctrine:fixtures:load --env=test'));
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
                $this->stringContains(UserEvents::USER_CREATED),
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
        $user->setPassword('toto');
        $manager->createCustomer($user);

        $this->assertCount(3, $client->getContainer()->get('doctrine')->getRepository(Customer::class)->findAll());
    }
}
