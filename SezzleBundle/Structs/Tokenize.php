<?php

namespace SezzlePayment\SezzleBundle\Structs;

use SezzlePayment\SezzleBundle\Structs\Tokenize\Customer;

class Tokenize
{
    /**
     * Scopes expressed in the form of resource URL endpoints. The value of the scope parameter
     * is expressed as a list of space-delimited, case-sensitive strings.
     *
     * @var Customer
     */
    private $customer;

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }


    /**
     * @param array $data
     * @return Tokenize
     */
    public static function fromArray(array $data)
    {
        $result = new self();

        if (array_key_exists('customer', $data)) {
            $result->setCustomer(Customer::fromArray($data['customer']));
        }

        return $result;
    }
}
