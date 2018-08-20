<?php

namespace App\Tests\entity;

use App\Entity\Address;
use PHPUnit\Framework\TestCase;

class AddressTest extends TestCase
{
    public function testCreateAddress()
    {
        $address = new Address();

        $this->assertInstanceOf(Address::class, $address);
        
        return $address;
    }

    /**
     * @depends  testCreateAddress
     */
    public function testReturnGetStreet($address)
    {
        $address->setStreet('rue du soleil');
        $street = $address->getStreet();

        $this->assertInternalType('string', $street);
    }

    /**
     * @depends  testCreateAddress
     */
    public function testReturnSetStreet($address)
    {
        $street = $address->setStreet('rue du soleil');

        $this->assertInstanceOf(Address::class, $street);
    }

    /*###### A tester avec la  base de donnée
     * @depends  testCreateAddress
     */
    /* public function testReturnGetId($address)
    {
        $id = $address->getId();

        $this->assertInternalType('int', $id);
    } */

    /**
     * @depends testCreateAddress
     */
    public function testReturnGetPostalCode($address)
    {
        $address->setPostalCode('75001');
        $postalCode = $address->getPostalCode();

        $this->assertInternalType('string', $postalCode);
    }

    /**
     * @depends  testCreateAddress
     */
    public function testReturnSetPostalCode($address)
    {
        $postalCode = $address->setPostalCode('77001');

        $this->assertInstanceOf(Address::class, $postalCode);
    }

    /**
     * @depends testCreateAddress
     */
    public function testReturnGetCity($address)
    {
        $address->setCity('Paris');
        $city = $address->getCity();

        $this->assertInternalType('string', $city);
    }

    /**
     * @depends  testCreateAddress
     */
    public function testReturnSetCity($address)
    {
        $city = $address->setCity('Melun');

        $this->assertInstanceOf(Address::class, $city);
    }

    /**
     * @depends testCreateAddress
     */
    public function testReturnGetCountry($address)
    {
        $address->setCountry('France');
        $country = $address->getCountry();

        $this->assertInternalType('string', $country);
    }

    /**
     * @depends  testCreateAddress
     */
    public function testReturnSetCountry($address)
    {
        $country = $address->setCountry('Belgique');

        $this->assertInstanceOf(Address::class, $country);
    }

    /**
     * @depends testCreateAddress
     */
    public function testReturnGetAddressCpl($address)
    {
        $address->setAddressCpl('Batiment B');
        $addressCpl = $address->getAddressCpl();

        $this->assertInternalType('string', $addressCpl);
    }

    /**
     * @depends  testCreateAddress
     */
    public function testReturnSetAddressCpl($address)
    {
        $addressCpl = $address->setAddressCpl('Porte 2 bis');

        $this->assertInstanceOf(Address::class, $addressCpl);
    }

    /**
     * @depends testCreateAddress
     */
    public function testReturnToString($address)
    {
        $toString = $address->__toString();

        $this->assertInternalType('string', $toString);
    }
}