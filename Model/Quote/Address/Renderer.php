<?php
namespace Sales\Quote\Model\Quote\Address;

use Magento\Customer\Model\Address\Config as AddressConfig;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Quote\Model\Quote\Address;
use Magento\Store\Model\ScopeInterface;
use Magento\Customer\Model\Address\Mapper;

/**
 * Class Renderer used for formatting a quote address
 */
class Renderer
{
    /**
     * Constructor
     *
     * @param AddressConfig             $addressConfig
     * @param EventManager              $eventManager
     * @param ScopeConfigInterface|null $scopeConfig
     * @param Mapper                    $addressMapper
     */
    public function __construct(
        protected AddressConfig $addressConfig,
        protected EventManager $eventManager,
        protected ScopeConfigInterface $scopeConfig,
        protected Mapper $addressMapper
    ) {
    }

    /**
     * Format address in a specific way
     *
     * @param Address $address
     * @param string  $type
     *
     * @return string|null
     */
    public function format(Address $address, string $type): ?string
    {
        $storeId = $address->getQuote()->getStoreId();
        $this->addressConfig->setStore($storeId);
        $formatType = $this->addressConfig->getFormatByCode($type);

        if (!$formatType || !$formatType->getRenderer()) {
            return null;
        }

        $addressData           = $address->getData();
        $addressData['locale'] = $this->getLocaleByStoreId((int)$storeId);
        $renderer              = $this->addressConfig->getFormatByCode($type)->getRenderer();

        return $renderer->renderArray($addressData);
    }

    /**
     * Returns locale by storeId
     *
     * @param int $storeId
     *
     * @return string
     */
    private function getLocaleByStoreId(int $storeId): string
    {
        return $this->scopeConfig->getValue(Data::XML_PATH_DEFAULT_LOCALE, ScopeInterface::SCOPE_STORE, $storeId);
    }
}
