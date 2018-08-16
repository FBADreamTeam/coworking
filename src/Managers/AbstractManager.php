<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 16/08/2018
 * Time: 12:05.
 */

namespace App\Managers;

use Doctrine\ORM\EntityManagerInterface;

class AbstractManager implements ManagerInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * AbstractManager constructor.
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
}
