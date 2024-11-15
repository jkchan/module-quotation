<?php
namespace Sales\Quote\Block\Adminhtml\Quote\Address;

use Magento\Backend\Block\Template\Context;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Model\Address\Mapper;
use Magento\Customer\Model\Metadata\FormFactory as CustomerFormFactory;
use Magento\Customer\Model\Options;
use Magento\Directory\Helper\Data;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Block\Adminhtml\Order\Create\Form\Address as AddressForm;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Customer\Helper\Address as CustomerHelperAddress;

class Form extends AddressForm
{
    protected $_template = 'Sales_Quote::quote/address/form.phtml';

    /**
     * @param Context                          $context
     * @param Quote                            $sessionQuote
     * @param Create                           $orderCreate
     * @param PriceCurrencyInterface           $priceCurrency
     * @param FormFactory                      $formFactory
     * @param DataObjectProcessor              $dataObjectProcessor
     * @param Data                             $directoryHelper
     * @param EncoderInterface                 $jsonEncoder
     * @param CustomerFormFactory              $customerFormFactory
     * @param Options                          $options
     * @param CustomerHelperAddress            $addressHelper
     * @param AddressRepositoryInterface       $addressService
     * @param SearchCriteriaBuilder            $criteriaBuilder
     * @param FilterBuilder                    $filterBuilder
     * @param Mapper                           $addressMapper
     * @param Registry                         $registry
     * @param array                            $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Quote $sessionQuote,
        Create $orderCreate,
        PriceCurrencyInterface $priceCurrency,
        FormFactory $formFactory,
        DataObjectProcessor $dataObjectProcessor,
        Data $directoryHelper,
        EncoderInterface $jsonEncoder,
        CustomerFormFactory $customerFormFactory,
        Options $options,
        CustomerHelperAddress $addressHelper,
        AddressRepositoryInterface $addressService,
        SearchCriteriaBuilder $criteriaBuilder,
        FilterBuilder $filterBuilder,
        Mapper $addressMapper,
        protected Registry $registry,
        array $data = []
    ) {

        parent::__construct(
            $context,
            $sessionQuote,
            $orderCreate,
            $priceCurrency,
            $formFactory,
            $dataObjectProcessor,
            $directoryHelper,
            $jsonEncoder,
            $customerFormFactory,
            $options,
            $addressHelper,
            $addressService,
            $criteriaBuilder,
            $filterBuilder,
            $addressMapper,
            $data
        );
    }

    /**
     * Order address getter
     *
     * @return Address
     */
    protected function getAddress(): Address
    {
        return $this->registry->registry('quote_address');
    }

    /**
     * Define form attributes (id, method, action)
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $this->_form->setId('edit_form');
        $this->_form->setClass('admin__fieldset');
        $this->_form->setMethod('post');
        $this->_form->setAction(
            $this->getUrl('sales_quote/*/addressSave', ['address_id' => $this->getAddress()->getId()])
        );
        $this->_form->setUseContainer(true);
        $postCodeElement = $this->_form->getElement('postcode');
        if ($postCodeElement) {
            $postCodeElement->setData('required', true);
        }
        return $this;
    }

    /**
     * Form header text getter
     *
     * @return Phrase
     */
    public function getHeaderText(): Phrase
    {
        return __(
            'Quote #%1 %2 Address Information',
            $this->getAddress()->getQuoteId(),
            $this->getAddress()->getAddressType() == 'shipping' ? __('Shipping') : __('Billing')
        );
    }

    /**
     * Return Form Elements values
     *
     * @return array
     */
    public function getFormValues(): array
    {
        return $this->getAddress()->getData();
    }

    /**
     * Return Address Store Id
     *
     * @return integer
     */
    protected function getAddressStoreId(): int
    {
        return $this->getAddress()->getQuote()->getStoreId();
    }
}
