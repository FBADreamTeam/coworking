<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 15/08/2018
 * Time: 17:28.
 */

namespace App\Exceptions;

class PriceException extends \Exception
{
    public const PRICE_LESS_OR_EQUAL_TO_0 = 'The price cannot be less than or equal to 0.';

    /**
     * PriceException constructor.
     *
     * @param string $message
     */
    public function __construct(string $message = self::PRICE_LESS_OR_EQUAL_TO_0)
    {
        parent::__construct($message);
    }
}
