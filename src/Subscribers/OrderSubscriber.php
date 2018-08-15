<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 26/07/2018
 * Time: 10:13.
 */

namespace App\Subscribers;

use App\Events\OrderEvents;
use App\Notifier\Notifier;
use App\Services\EmployeeService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderSubscriber implements EventSubscriberInterface
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
     * OrderSubscriber constructor.
     *
     * @param Notifier        $notifier
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
    public static function getSubscribedEvents()
    {
        return [
            OrderEvents::ORDER_PLACED => 'onOrderPlaced',
        ];
    }

    /**
     * @param OrderEvents $event
     */
    public function onOrderPlaced(OrderEvents $event): void
    {
        $order = $event->getOrder();
        $customer = $order->getBooking()->getCustomer();

        $message = 'Dear '.$customer->getFirstName().', votre commande a bien été enregistrée !';
        /**
         * TODO: add order summary in email.
         */
        $subject = 'Merci pour votre commande !';

        $this->notifier->notify($subject, $message, [$customer]);

        $message = 'Une nouvelle commande a été créé: '.PHP_EOL.PHP_EOL;
        $message .= '   - Nom: '.$customer->getLastName().PHP_EOL;
        $message .= '   - Prénom: '.$customer->getFirstName().PHP_EOL;
        $message .= '   - Nom d\'utilisateur: '.$customer->getUsername().PHP_EOL;
        $message .= '   - E-mail: '.$customer->getEmail().PHP_EOL;
        /**
         * TODO: add order summary here with customer info.
         */
        $subject = 'Une nouvelle commande a été créé';

        $this->notifier->notify($subject, $message, $this->employeeService->getAllEmployees());
        $this->notifier->log($subject, $message);
    }
}
