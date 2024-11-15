<?php
namespace Sales\Quote\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid as MagentoBackendGrid;

class Grid extends MagentoBackendGrid
{
    /**
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $this->getCollection()->addFieldToFilter('is_active', ['eq' => 1]);

        return parent::_prepareCollection();
    }
}
