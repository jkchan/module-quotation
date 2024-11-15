<?php
namespace Sales\Quote\Model\Quote;

use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\PaymentFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ShippingMethodManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterfaceFactory;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Manager
{
    /**
     * Dummy data. used when Store analog is empty.
     */
    protected const STREET    = 'street1';
    protected const CITY      = 'street1';
    protected const TELEPHONE = '+10111111111';
    protected const POST_CODE = '1111';
    protected const REGION_ID = '1';
    protected const DEFAULT_PAYMENT = 'checkmo';

    /**
     * @param ShippingMethodManagement            $shippingMethodManagement
     * @param CartManagementInterface             $cartManagement
     * @param PaymentFactory                      $paymentFactory
     * @param ShippingInformationManagement       $shippingInformationManagement
     * @param QuoteRepository                     $quoteRepository
     * @param ShippingInformationInterfaceFactory $shippingInformationInterfaceFactory
     * @param Session                             $session
     * @param ScopeConfigInterface                $scopeConfig
     * @param AccountManagementInterface          $accountManagement
     */
    public function __construct(
        protected ShippingMethodManagement $shippingMethodManagement,
        protected CartManagementInterface $cartManagement,
        protected PaymentFactory $paymentFactory,
        protected ShippingInformationManagement $shippingInformationManagement,
        protected QuoteRepository $quoteRepository,
        protected ShippingInformationInterfaceFactory $shippingInformationInterfaceFactory,
        protected Session $session,
        protected ScopeConfigInterface $scopeConfig,
        protected AccountManagementInterface $accountManagement
    ) {
    }

    /**
     * @param int  $quoteId
     * @param bool $force
     *
     * @return int
     * @throws CouldNotSaveException
     * @throws InputException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function prepareQuoteAndPlaceOrder(int $quoteId, bool $force = false): int
    {
        $quote = $this->quoteRepository->get($quoteId);

        if (!$quote->getCustomerId()) {
            $adminUser = $this->session->getUser();
            $quote->setCustomerFirstname($quote->getCustomerFirstname() ?: $adminUser->getFirstName());
            $quote->setCustomerLastname($quote->getCustomerLastname() ?: $adminUser->getLastName());
            $quote->setCustomerEmail($quote->getCustomerEmail() ?: $adminUser->getEmail());
            $quote->setCustomerIsGuest(true);
        }

        if ($force) {
            $this->prepareAddresses($quote);
        }

        $shippingInfo = $this->shippingMethodManagement->getList($quoteId);
        //use 1st available shipping.
        if ($shippingInfo) {
            $shipping = $shippingInfo[0];
        } else {
            throw new LocalizedException(__('Unable to place order. No shipping available.'));
        }

        $shippingInformation = $this->shippingInformationInterfaceFactory->create();
        $shippingInformation->setBillingAddress($quote->getBillingAddress());
        $shippingInformation->setShippingAddress($quote->getShippingAddress());
        $shippingInformation->setShippingMethodCode($shipping->getMethodCode());
        $shippingInformation->setShippingCarrierCode($shipping->getCarrierCode());
        $this->shippingInformationManagement->saveAddressInformation($quoteId, $shippingInformation);

        $payment = $this->paymentFactory->create();
        $payment->setMethod(self::DEFAULT_PAYMENT)
            ->setAdditionalInformation(
                'metadata',
                [
                    'type'       => 'free',
                    'fraudulent' => false,
                ]
            );

        return (int)$this->cartManagement->placeOrder($quoteId, $payment);
    }

    /**
     * @param Quote $quote
     *
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function prepareAddresses(Quote $quote)
    {
        $customerId             = $quote->getCustomerId();
        $defaultShippingAddress = $customerId ? $this->accountManagement->getDefaultShippingAddress($customerId) : null;
        $defaultBillingAddress  = $customerId ? $this->accountManagement->getDefaultBillingAddress($customerId) : null;
        $shippingAddress        = $quote->getShippingAddress();
        $billingAddress         = $quote->getBillingAddress();

        if (!$customerId || !$defaultBillingAddress || !$defaultShippingAddress) {
            $this->prepareAddressWithStoreData($shippingAddress, $quote->getStoreId());
            $this->prepareAddressWithStoreData($billingAddress, $quote->getStoreId());
        } else {
            $shippingAddress->addData($defaultShippingAddress->__toArray());
            $billingAddress->addData($defaultBillingAddress->__toArray());
        }
        $this->quoteRepository->save($quote);
    }

    /**
     * @param Address $address
     * @param int     $storeId
     *
     * @return Address
     */
    protected function prepareAddressWithStoreData(Address $address, int $storeId = 0): Address
    {
        $adminUser = $this->session->getUser();
        $address->setFirstname($adminUser->getFirstName());
        $address->setLastname($adminUser->getLastName());
        $address->setEmail($adminUser->getEmail());
        $address->setTelephone($this->getStoreInfo('general/store_information/phone', $storeId) ?: self::TELEPHONE);
        $address->setPostcode($this->getStoreInfo('general/store_information/postcode', $storeId) ?: self::POST_CODE);
        $address->setCity($this->getStoreInfo('general/store_information/city', $storeId) ?: self::CITY);
        $address->setCountryId($this->getStoreInfo('general/country/default', $storeId));
        $address->setRegionId($this->getStoreInfo('general/store_information/region_id', $storeId) ?: self::REGION_ID);
        $address->setStreet(
            [
                $this->getStoreInfo('general/store_information/street_line1', $storeId) ?: self::STREET,
                $this->getStoreInfo('general/store_information/street_line2', $storeId) ?: self::STREET,
            ]
        );

        return $address;
    }

    /**
     * @param string $config
     * @param int    $storeId
     * @param string $scope
     *
     * @return mixed
     */
    protected function getStoreInfo(string $config, int $storeId = 0, string $scope = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue($config, $scope, $storeId);
    }
}
