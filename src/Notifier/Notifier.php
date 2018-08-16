<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 25/07/2018
 * Time: 17:10.
 */

namespace App\Notifier;

use App\Entity\AbstractUser;
use Psr\Log\LoggerInterface;

class Notifier implements NotifierInterface
{
    use NotifierTrait;

    /**
     * @var string
     */
    private $customerServiceEmail;

    /**
     * NotifierTrait constructor.
     *
     * @param \Swift_Mailer   $mailer
     * @param LoggerInterface $logger
     * @param string          $customerServiceEmail
     */
    public function __construct(\Swift_Mailer $mailer, LoggerInterface $logger, string $customerServiceEmail)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->customerServiceEmail = $customerServiceEmail;
    }

    /**
     * @param string $subject
     * @param string $message
     * @param array  $users
     */
    public function notify(string $subject, string $message, array $users): void
    {
        $mail = new \Swift_Message($subject, $message);
        $mail->addFrom($this->customerServiceEmail);

        /*
         * @var AbstractUser
         */
        foreach ($users as $user) {
            $mail->addTo($user->getEmail());
        }

        $this->mailer->send($mail);
    }

    /**
     * @param string $subject
     * @param string $message
     *
     * @return mixed
     */
    public function log(string $subject, string $message)
    {
        $this->logger->info("[$subject] $message");
    }
}
