<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 25/07/2018
 * Time: 17:10
 */

namespace App\Notifier;


use App\Entity\AbstractUser;

class Notifier implements NotifierInterface
{
    use NotifierTrait;

    /**
     * @var string
     */
    private $customerServiceEmail;

    /**
     * Notifier constructor.
     * @param string $customerServiceEmail
     */
    public function __construct(string $customerServiceEmail)
    {
        $this->customerServiceEmail = $customerServiceEmail;
    }

    /**
     * @param string $subject
     * @param string $message
     * @param array $users
     */
    public function notify(string $subject, string $message, array $users): void
    {
        $mail = new \Swift_Message($subject, $message);
        $mail->addFrom($this->customerServiceEmail);
        /**
         * @var AbstractUser $user
         */
        foreach ($users as $user) {
            $mail->addTo($user->getEmail());
        }

        $this->mailer->send($mail);
    }

    /**
     * @param string $subject
     * @param string $message
     * @return mixed
     */
    public function log(string $subject, string $message)
    {
        $this->logger->info("[$subject] $message");
    }
}