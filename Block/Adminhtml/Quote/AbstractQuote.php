<?php
namespace Sales\Quote\Block\Adminhtml\Quote;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;

class AbstractQuote extends  Widget
{
    /**
     * @param Context          $context
     * @param Registry         $registry
     * @param array            $data
     */
    public function __construct(
        Context $context,
        protected Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context);
    }

    /**
     * Retrieve available quote
     *
     * @return Quote
     * @throws LocalizedException
     */
    public function getQuote(): Quote
    {
        if ($this->hasQuote()) {
            return $this->getData('quote');
        }
        if ($this->coreRegistry->registry('current_quote')) {
            return $this->coreRegistry->registry('current_quote');
        }
        if ($this->coreRegistry->registry('quote')) {
            return $this->coreRegistry->registry('quote');
        }
        throw new LocalizedException(__('We can\'t get the quote instance right now.'));
    }
}
