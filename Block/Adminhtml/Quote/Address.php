<?php
namespace Sales\Quote\Block\Adminhtml\Quote;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

class Address extends Container
{
    /**
     * @param Context  $context
     * @param Registry $registry
     * @param array    $data
     */
    public function __construct(
        Context $context,
        protected Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_quote';
        $this->_mode       = 'address';
        $this->_blockGroup = 'Sales_Quote';
        parent::_construct();
        $this->updateButton('save', 'label', __('Save Quote Address'));
        $this->removeButton('delete');
        $this->removeButton('reset');

    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return Phrase
     */
    public function getHeaderText(): Phrase
    {
        $address = $this->registry->registry('quote_address');
        $quoteId = $address->getQuote()->getId();
        if ($address->getAddressType() == 'shipping') {
            $type = __('Shipping');
        } else {
            $type = __('Billing');
        }

        return __('Edit Quote %1 %2 Address', $quoteId, $type);
    }

    /**
     * Back button url getter
     *
     * @return string
     */
    public function getBackUrl(): string
    {
        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $this->registry->registry('quote_address');

        return $this->getUrl('sales_quote/*/view', ['quote_id' => $address ? $address->getQuote()->getId() : null]);
    }
}
