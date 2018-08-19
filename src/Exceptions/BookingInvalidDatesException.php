<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 18/08/2018
 * Time: 19:25.
 */

namespace App\Exceptions;

class BookingInvalidDatesException extends \Exception
{
    public const START_DATE_AFTER_END_DATE = 'The booking start date cannot be after the end date.';

    public const DATES_BEFORE_NOW = 'The booking start and end dates cannot be before now.';

    /**
     * BookingInvalidDatesException constructor.
     *
     * @param string $message
     */
    public function __construct(string $message = self::START_DATE_AFTER_END_DATE)
    {
        parent::__construct($message);
    }
}
