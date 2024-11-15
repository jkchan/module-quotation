<?php
namespace Sales\Quote\Model;

use Magento\Quote\Model\Quote as MagentoQuote;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\Model\Context as MagentoFrameworkContext;
use Magento\Framework\Registry as MagentoFrameworkRegistry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Quote\Model\QuoteValidator;
use Magento\Catalog\Helper\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory as MagentoQuoteItemCollection;
use Magento\Quote\Model\Quote\ItemFactory;
use Magento\Framework\Message\Factory as MagentoFrameworkMessage;
use Magento\Sales\Model\Status\ListFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Quote\Model\Quote\PaymentFactory;
use Magento\Quote\Model\ResourceModel\Quote\Payment\CollectionFactory as MagentoQuotePaymentCollection;
use Magento\Framework\DataObject\Copy;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Quote\Model\Quote\Item\Processor;
use Magento\Framework\DataObject\Factory as MagentoFrameworkDataObjectFactory;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Quote\Model\Cart\CurrencyFactory as MagentoQuoteCurrencyFactory;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Quote\Model\Quote\TotalsCollector;
use Magento\Quote\Model\Quote\TotalsReader;
use Magento\Quote\Model\ShippingFactory;
use Magento\Quote\Model\ShippingAssignmentFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Sales\Model\OrderIncrementIdChecker;
use Magento\Directory\Model\AllowedCountries;

class Quote extends MagentoQuote
{
    protected $quoteCurrency;

    public function __construct(
        protected CurrencyFactory $globCurrencyFactory,
        MagentoFrameworkContext $context,
        MagentoFrameworkRegistry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        QuoteValidator $quoteValidator,
        Product $catalogProduct,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AddressFactory $quoteAddressFactory,
        CustomerFactory $customerFactory,
        GroupRepositoryInterface $groupRepository,
        MagentoQuoteItemCollection $quoteItemCollectionFactory,
        ItemFactory $quoteItemFactory,
        MagentoFrameworkMessage $messageFactory,
        ListFactory $statusListFactory,
        ProductRepositoryInterface $productRepository,
        PaymentFactory $quotePaymentFactory,
        MagentoQuotePaymentCollection $quotePaymentCollectionFactory,
        Copy $objectCopyService,
        StockRegistryInterface $stockRegistry,
        Processor $itemProcessor,
        MagentoFrameworkDataObjectFactory $objectFactory,
        AddressRepositoryInterface $addressRepository,
        SearchCriteriaBuilder $criteriaBuilder,
        FilterBuilder $filterBuilder,
        AddressInterfaceFactory $addressDataFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerRepositoryInterface $customerRepository,
        DataObjectHelper $dataObjectHelper,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        MagentoQuoteCurrencyFactory $currencyFactory,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        TotalsCollector $totalsCollector,
        TotalsReader $totalsReader,
        ShippingFactory $shippingFactory,
        ShippingAssignmentFactory $shippingAssignmentFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = [],
        OrderIncrementIdChecker $orderIncrementIdChecker = null,
        AllowedCountries $allowedCountriesReader = null
    ) {
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $quoteValidator, $catalogProduct,
            $scopeConfig, $storeManager, $scopeConfig, $quoteAddressFactory, $customerFactory, $groupRepository, $quoteItemCollectionFactory,
            $quoteItemFactory, $messageFactory, $statusListFactory, $productRepository, $quotePaymentFactory, $quotePaymentCollectionFactory,
            $objectCopyService, $stockRegistry, $itemProcessor, $objectFactory, $addressRepository, $criteriaBuilder, $filterBuilder,
            $addressDataFactory, $customerDataFactory, $customerRepository, $dataObjectHelper, $extensibleDataObjectConverter,
            $currencyFactory, $extensionAttributesJoinProcessor, $totalsCollector, $totalsReader, $shippingFactory,
            $shippingAssignmentFactory, $resource, $resourceCollection, $data, $orderIncrementIdChecker, $allowedCountriesReader
        );
    }


    /**
     * Is currency different
     *
     * @return bool
     */
    public function isCurrencyDifferent()
    {
        return $this->getQuoteCurrencyCode() != $this->getBaseCurrencyCode();
    }

    /**
     * Get formatted price value including order currency rate to order website currency
     *
     * @param float $price
     * @param bool $addBrackets
     * @return string
     */
    public function formatPrice($price, $addBrackets = false)
    {
        return $this->formatPricePrecision($price, 2, $addBrackets);
    }

    /**
     * Format price precision
     *
     * @param float $price
     * @param int $precision
     * @param bool $addBrackets
     * @return string
     */
    public function formatPricePrecision($price, $precision, $addBrackets = false)
    {
        return $this->getQuoteCurrency()->formatPrecision($price, $precision, [], true, $addBrackets);
    }

    /**
     * Get currency model instance. Will be used currency with which order placed
     *
     * @return \Magento\Directory\Model\Currency
     */
    public function getQuoteCurrency()
    {
        if ($this->quoteCurrency === null) {
            $this->quoteCurrency = $this->globCurrencyFactory->create();
            $this->quoteCurrency->load($this->getQuoteCurrencyCode());
        }
        return $this->quoteCurrency;
    }

}