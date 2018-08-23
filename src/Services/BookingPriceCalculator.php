<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 08/08/2018
 * Time: 16:47.
 */

namespace App\Services;

use App\Entity\Booking;
use App\Entity\BookingOptions;

class BookingPriceCalculator
{
    public const HOURS_FORMAT = 'H:i:s';

    /**
     * @var \DateTime
     */
    protected $businessHourStart;

    /**
     * @var \DateTime
     */
    protected $businessHourEnd;

    /**
     * BookingPriceCalculator constructor.
     *
     * @param string $businessHourStart
     * @param string $businessHourEnd
     */
    public function __construct(string $businessHourStart, string $businessHourEnd)
    {
        $this->businessHourStart = new \DateTime($businessHourStart);
        $this->businessHourEnd = new \DateTime($businessHourEnd);
    }

    /**
     * @param Booking $booking
     *
     * @return int
     *
     * @throws \Exception
     */
    public function calculateTotalPrice(Booking $booking): int
    {
        // get number of business days
        $days = $this->getBusinessDaysCount($booking->getStartDate(), $booking->getEndDate());
        // calculate total HT price without options
        $price = $this->calculateTotalPriceWithoutOptions($booking);
        // get the booking options
        $options = $booking->getBookingOptions();
        // get the total price of booking options
        /** @var BookingOptions $option */
        foreach ($options as $option) {
            $optionTotal = $option->getPrice() * $option->getQuantity() * $days;
            $option->setTotal($optionTotal);
            $price += $option->getTotal();
        }

        return $price;
    }

    /**
     * @param Booking $booking
     *
     * @return int|null
     *
     * @throws \Exception
     */
    public function calculateTotalPriceWithoutOptions(Booking $booking): ?int
    {
        if (null === $booking->getStartDate() || null === $booking->getEndDate()) {
            return null;
        }

        $days = $this->getBusinessDaysCount($booking->getStartDate(), $booking->getEndDate());
        $price = 0;
        $midnight = (new \DateTime('00:00:00'))->format(self::HOURS_FORMAT);

        if ($days > 1) {
            // months
            $price += (int) floor($days / 20) * $booking->getRoom()->getMonthlyPrice();
            $days -= (int) floor($days / 20) * 20;

            // weeks
            $price += (int) floor($days / 5) * $booking->getRoom()->getWeeklyPrice();
            $days -= (int) floor($days / 5) * 5;

            // days
            $price += $days * $booking->getRoom()->getDailyPrice();
        } elseif (
            (
                $booking->getStartDate()->format(self::HOURS_FORMAT) === $midnight
                && $booking->getEndDate()->format(self::HOURS_FORMAT) === $midnight
            )
            ||
            (
                $booking->getStartDate()->format(self::HOURS_FORMAT) === $this->businessHourStart->format(self::HOURS_FORMAT)
                && $booking->getEndDate()->format(self::HOURS_FORMAT) === $this->businessHourEnd->format(self::HOURS_FORMAT)
            )
        ) {
            $price = $booking->getRoom()->getDailyPrice();
        } else {
            $interval = $booking->getStartDate()->diff($booking->getEndDate());
            $price = $interval->h * $booking->getRoom()->getHourlyPrice();
        }

        return $price;
    }

    /**
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     *
     * @return int
     *
     * @throws \Exception
     */
    private function getBusinessDaysCount(\DateTimeInterface $startDate, \DateTimeInterface $endDate): int
    {
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($startDate, $interval, $endDate);
        $count = 0;

        /** @var \DateTime $day */
        foreach ($period as $day) {
            // Get the day as a numeric index (0 = Monday ... 7 = Sunday)
            $numericDay = $day->format('N');
            // if the is NOT a Saturday nor a Sunday
            if ('6' !== $numericDay && '7' !== $numericDay) {
                ++$count;
            }
        }

        return $count;
    }
}
