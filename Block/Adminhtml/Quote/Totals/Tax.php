<?php
namespace Sales\Quote\Block\Adminhtml\Quote\Totals;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Block\Adminhtml\Order\Invoice\Totals;
use Magento\Sales\Block\Adminhtml\Order\Totals\Tax as MagentoSalesTax;

class Tax extends MagentoSalesTax
{
    /**
     * Initialize all order totals relates with tax
     *
     * @return \Magento\Tax\Block\Sales\Order\Tax
     * @throws NoSuchEntityException
     */
    public function initTotals()
    {
        /** @var $parent Totals */
        $parent = $this->getParentBlock();
        $source = $parent->getSource();
        $store  = $this->_storeManager->getStore($source->getStoreId());
        $source->setStore($store);
        $source->setOrder($source);
        $this->_order  = $parent->getSource();
        $this->_source = $parent->getSource();

        $allowTax   = $this->_source->getTaxAmount() > 0 || $this->_config->displaySalesZeroTax($store);
        $grandTotal = (double)$this->_source->getGrandTotal();
        if (!$grandTotal || $allowTax && !$this->_config->displaySalesTaxWithGrandTotal($store)) {
            $this->_addTax();
        }

        $this->_initSubtotal();
        $this->_initDiscount();
        $this->_initGrandTotal();

        return $this;
    }
}
