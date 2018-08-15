<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 25/07/2018
 * Time: 16:51
 */

namespace App\Events;

use App\Entity\AbstractUser;
use Symfony\Component\EventDispatcher\Event;

class UserEvents extends Event
{
    public const USER_CREATED = 'user.created';

    /**
     * @var AbstractUser $user
     */
    protected $user;

    /**
     * UserEvents constructor.
     * @param AbstractUser $user
     */
    public function __construct(AbstractUser $user)
    {
        $this->user = $user;
    }

    /**
     * @return AbstractUser
     */
    public function getUser(): AbstractUser
    {
        return $this->user;
    }
}
