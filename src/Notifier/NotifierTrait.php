<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 25/07/2018
 * Time: 16:58.
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
}
