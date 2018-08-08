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

class UserCreatedEvent extends Event
{
    const NAME = 'user.created';

    /**
     * @var AbstractUser $user
     */
    protected $user;

    /**
     * UserCreatedEvent constructor.
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
