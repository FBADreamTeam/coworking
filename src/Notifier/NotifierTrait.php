<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 25/07/2018
 * Time: 16:58
 */

namespace App\Notifier;


use Psr\Log\LoggerInterface;

trait NotifierTrait
{
    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * NotifierTrait constructor.
     * @param \Swift_Mailer $mailer
     * @param LoggerInterface $logger
     */
    public function __construct(\Swift_Mailer $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }
}