<?php
/**
 * Created by PhpStorm.
 * User: brahim
 * Date: 31/07/2018
 * Time: 17:29
 */

namespace App\Managers;

use App\Entity\Address;
use App\Entity\Customer;
use App\Events\UserCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CustomerManager
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    private $repository;

    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    public function __construct(EntityManagerInterface $em, EventDispatcherInterface $dispatcher)
    {
        $this->em = $em;
        $this->dispatcher = $dispatcher;
        $this->repository = $this->em->getRepository(Customer::class);
    }

    /**
     * @return Customer[]|\App\Entity\Employee[]|object[]
     */
    public function listCustomer(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @param Customer $customer
     * @return void
     */
    public function createCustomer(Customer $customer): void
    {
        $this->em->persist($customer);
        $this->em->flush();
        $event = new UserCreatedEvent($customer);
        $this->dispatcher->dispatch(UserCreatedEvent::NAME, $event);
    }

    /**
     * @param Address $address
     * @return void
     */
    public function addAddressCustomer(Address $address): void
    {
        $this->em->persist($address);
        $this->em->flush();
    }

    /**
     * @return void
     */
    public function updateCustomer(): void
    {
        $this->em->flush();
    }

    /**
     * @param Address $address
     * @return void
     */
    public function deleteAddressCustomer(Address $address): void
    {
        $this->em->remove($address);
        $this->em->flush();
    }

    /**
     * Gestion des doublons par email
     * @param string $email
     * @return bool
     */
    public function checkDuplicateEmail(string $email): bool
    {
        return $this->repository->findByEmail($email) ? true : false;
    }

    /**
     * @param string $email
     * @return bool
     */
    public function checkTokenExist(string $email): bool
    {
        $customer = $this->repository->findOneBy(['email'=>$email]);

        return $customer->getToken() ? true : false;
    }

    /**
     * @param int $id
     * @param string $token
     * @return bool
     */
    public function checkTokenValid(int $id, string $token): bool
    {
        $customer = $this->repository->find($id);

        return ($customer->getToken() === $token) ? true : false;
    }

    /**
     * @param Customer $customer
     * @param string $token
     */
    public function insertToken(Customer $customer, string $token): void
    {
        $dateToday = new \DateTime('now');
        $dateExpired = new \DateTime('now +2 hours');

        $customer->setToken($token);
        $customer->setCreatedToken($dateToday);
        $customer->setExpiredToken($dateExpired);

        $this->em->persist($customer);
        $this->em->flush();
    }

    /**
     * @param Customer $customer
     */
    public function resetToken(Customer $customer): void
    {
        $customer->setToken('');
        $customer->setCreatedToken(null);
        $customer->setExpiredToken(null);

        $this->em->flush();
    }

    /**
     * @param string $email
     * @param string $mailer
     * @param string $linkResetPassword
     * @throws \Exception
     */
    public function sendMessageGetPassword(string $email, \Swift_Mailer $mailer, string $linkResetPassword)
    {
        $message = (new \Swift_Message('Réinitialisation de votre mote de passe'))
            ->setFrom('contact@dtcw.xyz')
            ->setTo($email)
            ->setBody(
                "Bonjour," . PHP_EOL .
                "Veuillez cliquer sur le lien suivant pour réinitialiser votre mot de passe. Le lien est valide durant 2 heures." . PHP_EOL .
                '<a href="' . $linkResetPassword . '">Réinitialiser votre mot depasse</a>'
            );

        try {
            $mailer->send($message);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
