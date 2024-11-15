<?php
namespace Sales\Quote\Model\Quote\Address;

use Magento\Quote\Model\Quote\Address;

class Validator
{
    /**
     * constant values for Mandatory Address Field
     */
    const MANDATORY_ADDRESS_FIELDS = [
        'firstname',
        'lastname',
        'city',
        'zip',
        'country',
        'street',
        'telephone',
    ];

    /**
     * @param Address $address
     *
     * @return bool
     */
    public function validate(Address $address): bool
    {
        $result = true;
        foreach ($address->getData() as $key => $value) {
            if (in_array($key, self::MANDATORY_ADDRESS_FIELDS) && !$value) {
                $result = false;
                break;
            }
        }

        return $result;
    }
}
