<?php
namespace Sales\Quote\Block\Adminhtml\Quote\View;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;
use Magento\Backend\Block\Widget\Tabs as MagentoBackendTabs;

class Tabs extends MagentoBackendTabs
{
    /**
     * @param Context          $context
     * @param EncoderInterface $jsonEncoder
     * @param Session          $authSession
     * @param Registry         $registry
     * @param array            $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        protected Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $jsonEncoder, $authSession, $data);
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

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_quote_view_tabs');
        $this->setDestElementId('sales_quote_view');
        $this->setTitle(__('Quote View'));
    }
}
