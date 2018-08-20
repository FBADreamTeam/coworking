<?php

namespace App\Tests\entity;

use App\Entity\Address;
use App\Entity\Booking;
use App\Entity\Customer;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    public function testCreatedCustomer()
    {
        $customer = new Customer();

        $this->assertInstanceOf(Customer::class, $customer);
    }


    public function testRemoveAddress()
    {
        $customer = new Customer();

        $address1 = new Address();
        $address2 = new Address();

        $customer->addAddress($address1);
        $customer->addAddress($address2);

        $customer->removeAddress($address2);

        $this->assertCount(1, $customer->getAddresses());
    }


    public function testRemoveBooking()
    {
        $customer = new Customer();

        $booking1 = new Booking;
        $booking2 = new Booking;

        $customer->addBooking($booking1);
        $customer->addBooking($booking2);

        $customer->removeBooking($booking2);

        $this->assertCount(1, $customer->getBookings());
    }

    public function testRoleCustomer()
    {
        $customer = new Customer();

        $this->assertSame('ROLE_USER', $customer->getRoles()[0]);
    }
}