<?php
/**
 * Created by PhpStorm.
 * User: Fried
 * Date: 16/08/2018
 * Time: 11:55.
 */

namespace App\Managers;

use App\Entity\Address;
use App\Entity\Customer;

class AddressManager extends AbstractManager
{
    /**
     * @param Customer $customer
     *
     * @return Address
     */
    public function getNewAddressWithCustomer(Customer $customer): Address
    {
        $address = new Address();
        $address->setCustomer($customer);

        return $address;
    }

    /**
     * @param Address $address
     */
    public function handleCreateRequest(Address $address): void
    {
        $customer = $address->getCustomer();
        $customer->addAddress($address);
        $this->em->persist($address);
        $this->em->persist($customer);
        $this->em->flush();
    }
}
