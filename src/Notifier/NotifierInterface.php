<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 25/07/2018
 * Time: 16:56.
 */

namespace App\Notifier;

interface NotifierInterface
{
    public function notify(string $subject, string $message, array $users): void;

    public function log(string $subject, string $message);
}
