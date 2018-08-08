<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 25/07/2018
 * Time: 16:48
 */

namespace App\Events;

use App\Entity\Order;
use Symfony\Component\EventDispatcher\Event;

class OrderPlacedEvent extends Event
{
    const NAME = 'order.placed';

    /**
     * @var Order $order
     */
    protected $order;

    /**
     * OrderPlacedEvent constructor.
     * @param Order $order
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }
}
