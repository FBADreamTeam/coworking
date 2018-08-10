<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 08/08/2018
 * Time: 16:47
 */

namespace App\Services;


use App\Entity\Booking;
use App\Entity\BookingOptions;

class BookingPriceCalculator
{
    const HOURS_FORMAT = 'H:i:s';

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
     * @return int
     * @throws \Exception
     */
    public function calculateTotalPrice(Booking $booking): int
    {
        $days = $this->getBusinessDaysCount($booking->getStartDate(), $booking->getEndDate());
        $price = $this->calculateTotalPriceWithoutOptions($booking);

        $options = $booking->getBookingOptions();

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
     * @return int|null
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

//        dump($days);

        if ($days > 1) {
//            dump($days);

            // months
//            dump((int)floor($days / 20));
            $price += (int)floor($days / 20) * $booking->getRoom()->getMonthlyPrice();
            $days -= (int)floor($days / 20) * 20;

//            dump($days);

            // weeks
            $price += (int)floor($days / 5) * $booking->getRoom()->getWeeklyPrice();
            $days -= (int)floor($days / 5) * 5;

//            dump($days);

            // days
            $price += $days * $booking->getRoom()->getDailyPrice();
        } else {
//            dump($booking->getStartDate()->format(self::HOURS_FORMAT));
//            dump($this->businessHourStart->format(self::HOURS_FORMAT));
//            dump($booking->getEndDate()->format(self::HOURS_FORMAT));
//            dump($this->businessHourEnd->format(self::HOURS_FORMAT));
//            dump($booking->getStartDate()->format(self::HOURS_FORMAT) === $this->businessHourStart->format(self::HOURS_FORMAT));
//            dump($booking->getEndDate()->format(self::HOURS_FORMAT) === $this->businessHourEnd->format(self::HOURS_FORMAT));
//            dump($booking->getStartDate()->format(self::HOURS_FORMAT) === $midnight);
//            dump($booking->getEndDate()->format(self::HOURS_FORMAT) === $midnight);
            if (
                (
                    $booking->getStartDate()->format(self::HOURS_FORMAT) === $midnight
                    && $booking->getEndDate()->format(self::HOURS_FORMAT) === $midnight
                )
                ||
                (
                    $booking->getStartDate()->format(self::HOURS_FORMAT) === $this->businessHourStart->format(self::HOURS_FORMAT)
                    && $booking->getEndDate()->format(self::HOURS_FORMAT) === $this->businessHourEnd->format(self::HOURS_FORMAT)
                )
            )
            {
                $price = $booking->getRoom()->getDailyPrice();
            } else {
                $interval = $booking->getStartDate()->diff($booking->getEndDate());
                $price = $interval->h * $booking->getRoom()->getHourlyPrice();
            }
        }

        return $price;
    }

    /**
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @return int
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
            if ($numericDay !== '6' && $numericDay !== '7') {
                $count++;
            }
        }

        return $count;
    }
}