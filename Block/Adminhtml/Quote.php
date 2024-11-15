<?php
namespace Sales\Quote\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Quote extends Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_quote';
        $this->_blockGroup = 'Sales_Quote';
        $this->_headerText = __('Quotes');
        parent::_construct();

        $this->removeButton('add');
    }
}
