<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 25/07/2018
 * Time: 16:53
 */

namespace App\Subscribers;


use App\Events\UserCreatedEvent;
use App\Notifier\Notifier;
use App\Services\EmployeeService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserSubscriber implements EventSubscriberInterface
{
    /**
     * @var Notifier
     */
    private $notifier;

    /**
     * @var EmployeeService
     */
    private $employeeService;

    /**
     * UserSubscriber constructor.
     * @param Notifier $notifier
     * @param EmployeeService $service
     */
    public function __construct(Notifier $notifier, EmployeeService $service)
    {
        $this->notifier = $notifier;
        $this->employeeService = $service;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            UserCreatedEvent::NAME => 'onUserCreated'
        ];
    }

    /**
     * @param UserCreatedEvent $event
     * @return void
     */
    public function onUserCreated(UserCreatedEvent $event): void
    {
        $user = $event->getUser();
        $message = 'Dear ' . $user->getFirstName() . ', votre compte a bien été créé! Bienvenu(e) chez Dream Team Coworking!';

        $subject = 'Bienvenu(e) chez Dream Team Coworking!';

        $this->notifier->notify($subject, $message, [$user]);

        $message = 'Un nouveau compte utilisateur a été créé: ' . PHP_EOL;
        $message .= '   - Nom: ' . $user->getLastName() . PHP_EOL;
        $message .= '   - Prénom: ' . $user->getFirstName() . PHP_EOL;
        $message .= '   - Nom d\'utilisateur: ' . $user->getUsername() . PHP_EOL;
        $message .= '   - E-mail: ' . $user->getEmail() . PHP_EOL;
        /**
         * TODO: add user admin profile url
         */

        $subject = 'Un nouveau compte utilisateur a été créé';

        $this->notifier->notify($subject, $message, $this->employeeService->getAllEmployees());
        $this->notifier->log($subject, $message);
    }
}