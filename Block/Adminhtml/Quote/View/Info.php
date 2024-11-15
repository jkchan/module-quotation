<?php
namespace Sales\Quote\Block\Adminhtml\Quote\View;

use DateTime;
use Exception;
use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Metadata\ElementFactory;
use Magento\Eav\Model\AttributeDataFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Address;
use Sales\Quote\Model\Quote\Address\Renderer;
use Magento\Store\Model\ScopeInterface;
use Sales\Quote\Block\Adminhtml\Quote\AbstractQuote;
use Sales\Quote\Model\Quote\Address\Validator;

class Info extends AbstractQuote
{
    /**
     * Constructor
     *
     * @param Context                   $context
     * @param Registry                  $registry
     * @param GroupRepositoryInterface  $groupRepository
     * @param CustomerMetadataInterface $metadata
     * @param ElementFactory            $elementFactory
     * @param Renderer                  $addressRenderer
     * @param Validator                 $addressValidator
     * @param array                     $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        protected GroupRepositoryInterface $groupRepository,
        protected CustomerMetadataInterface $metadata,
        protected ElementFactory $metadataElementFactory,
        protected Renderer $addressRenderer,
        protected Validator $addressValidator,
        array $data = []
    ) {
        parent::__construct($context, $registry, $data);
    }

    /**
     * Retrieve required options from parent
     *
     * @return void
     * @throws LocalizedException
     */
    protected function _beforeToHtml()
    {
        if (!$this->getParentBlock()) {
            throw new LocalizedException(
                __('Please correct the parent block for this block.')
            );
        }
        $this->setQuote($this->getParentBlock()->getQuote());

        foreach ($this->getParentBlock()->getQuoteInfoData() as $key => $value) {
            $this->setDataUsingMethod($key, $value);
        }

        parent::_beforeToHtml();
    }

    /**
     * Get quote store name
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuoteStoreName(): string
    {
        if ($this->getQuote()) {
            $storeId = $this->getQuote()->getStoreId();
            if ($storeId === null) {
                $deleted = __(' [deleted]');

                return nl2br($this->getQuote()->getStoreName()) . $deleted;
            }
            $store = $this->_storeManager->getStore($storeId);
            $name  = [$store->getWebsite()->getName(), $store->getGroup()->getName(), $store->getName()];

            return implode('<br/>', $name);
        }

        return '';
    }

    /**
     * Return name of the customer group.
     *
     * @return string
     * @throws LocalizedException
     */
    public function getCustomerGroupName(): string
    {
        if ($this->getQuote()) {
            $customerGroupId = $this->getQuote()->getCustomerGroupId();
            try {
                if ($customerGroupId !== null) {
                    return $this->groupRepository->getById($customerGroupId)->getCode();
                }
            } catch (NoSuchEntityException $e) {
                return '';
            }
        }

        return '';
    }

    /**
     * Get URL to edit the customer.
     *
     * @return string
     * @throws LocalizedException
     */
    public function getCustomerViewUrl(): string
    {
        if ($this->getQuote()->getCustomerIsGuest() || !$this->getQuote()->getCustomerId()) {
            return '';
        }

        return $this->getUrl('customer/index/edit', ['id' => $this->getQuote()->getCustomerId()]);
    }

    /**
     * Get quote save URL.
     *
     * @return string
     */
    public function getSaveUrl(): string
    {
        return $this->getUrl('sales_quote/quote/save');
    }

    /**
     * Find sort quote for account data
     * Sort Quote used as array key
     *
     * @param array $data
     * @param int   $sortQuote
     *
     * @return int
     */
    protected function _prepareAccountDataSortQuote(array $data, int $sortQuote)
    {
        if (isset($data[$sortQuote])) {
            return $this->_prepareAccountDataSortQuote($data, $sortQuote + 1);
        }

        return $sortQuote;
    }

    /**
     * Return array of additional account data
     * Value is option style array
     *
     * @return array
     * @throws LocalizedException
     */
    public function getCustomerAccountData(): array
    {
        $accountData = [];
        $entityType  = 'customer';

        foreach ($this->metadata->getAllAttributesMetadata() as $attribute) {
            if (!$attribute->isVisible() || $attribute->isSystem()) {
                continue;
            }
            $quoteKey   = sprintf('customer_%s', $attribute->getAttributeCode());
            $quoteValue = $this->getQuote()->getData($quoteKey);
            if ($quoteValue != '') {
                $metadataElement         = $this->metadataElementFactory->create($attribute, $quoteValue, $entityType);
                $value                   = $metadataElement->outputValue(AttributeDataFactory::OUTPUT_FORMAT_HTML);
                $sortQuote               = $attribute->getSortOrder() + $attribute->isUserDefined() ? 200 : 0;
                $sortQuote               = $this->_prepareAccountDataSortQuote($accountData, $sortQuote);
                $accountData[$sortQuote] = [
                    'label' => $attribute->getFrontendLabel(),
                    'value' => $this->escapeHtml($value, ['br']),
                ];
            }
        }
        ksort($accountData, SORT_NUMERIC);

        return $accountData;
    }

    /**
     * Get link to edit quote address page
     *
     * @param Address $address
     * @param string  $label
     *
     * @return string
     */
    public function getAddressEditLink(Address $address, string $label = ''): string
    {
        if ($this->_authorization->isAllowed('Magento_Sales::actions_edit')) {
            if (empty($label)) {
                $label = __('Edit');
            }
            $url = $this->getUrl('sales_quote/quote/address', ['address_id' => $address->getId()]);

            return '<a href="' . $this->escapeUrl($url) . '">' . $this->escapeHtml($label) . '</a>';
        }

        return '';
    }

    /**
     * Whether Customer IP address should be displayed on sales documents
     *
     * @return bool
     * @throws LocalizedException
     */
    public function shouldDisplayCustomerIp(): bool
    {
        return !$this->_scopeConfig->isSetFlag(
            'sales/general/hide_customer_ip',
            ScopeInterface::SCOPE_STORE,
            $this->getQuote()->getStoreId()
        );
    }

    /**
     * Check if is single store mode
     *
     * @return bool
     */
    public function isSingleStoreMode(): bool
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * Get timezone for store
     *
     * @param mixed $store
     *
     * @return string
     */
    public function getTimezoneForStore($store): string
    {
        return $this->_localeDate->getConfigTimezone(
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );
    }

    /**
     * Get object created at date
     *
     * @param string $createdAt
     *
     * @return DateTime
     * @throws Exception
     */
    public function getQuoteAdminDate($createdAt): DateTime
    {
        return $this->_localeDate->date(new DateTime($createdAt));
    }

    /**
     * Returns string with formatted address
     *
     * @param Address $address
     *
     * @return string
     */
    public function getFormattedAddress(Address $address): ?string
    {
        $result = '';
        if ($this->isValidAddress($address)) {
            $result = $this->addressRenderer->format($address, 'html');
        }

        return $result;
    }

    /**
     * @param mixed $alias
     * @param boolean $useCache
     * @return string
     */
    public function getChildHtml($alias = '', $useCache = true): ?string
    {
        $layout = $this->getLayout();

        if ($alias || !$layout) {
            return parent::getChildHtml($alias, $useCache);
        }

        $childNames       = $layout->getChildNames($this->getNameInLayout());
        $outputChildNames = array_diff($childNames, ['extra_customer_info']);

        $out = '';
        foreach ($outputChildNames as $childName) {
            $out .= $layout->renderElement($childName, $useCache);
        }

        return $out;
    }

    /**
     * @param Address $address
     * @return bool
     */
    public function isValidAddress(Address $address): bool
    {
        return $this->addressValidator->validate($address);
    }
}
