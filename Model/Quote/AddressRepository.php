<?php
namespace Sales\Quote\Model\Quote;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\ResourceModel\Quote\Address as AddressResource;

class AddressRepository
{
    /**
     * @param AddressResource $addressResource
     * @param AddressFactory  $addressFactory
     */
    public function __construct(
        protected AddressResource $addressResource,
        protected AddressFactory $addressFactory
    ) {
    }

    /**
     * @param Address $address
     *
     * @return AddressResource
     * @throws AlreadyExistsException
     */
    public function save(Address $address): AddressResource
    {
        return $this->addressResource->save($address);
    }

    /**
     * @param int $id
     *
     * @return Address
     */
    public function get(int $id): Address
    {
        $address = $this->addressFactory->create();
        $this->addressResource->load($address, $id);

        return $address;
    }
}
