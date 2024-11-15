<?php
namespace Sales\Quote\Block\Adminhtml\Quote\View\Tab;

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Sales\Quote\Block\Adminhtml\Quote\AbstractQuote;

class Info extends AbstractQuote implements TabInterface
{
    /**
     * Retrieve source model instance
     *
     * @return Quote
     * @throws LocalizedException
     */
    public function getSource(): Quote
    {
        return $this->getQuote();
    }

    /**
     * Get quote info data
     *
     * @return array
     */
    public function getQuoteInfoData(): array
    {
        return ['no_use_quote_link' => true];
    }

    /**
     * Get items html
     *
     * @return string
     */
    public function getItemsHtml(): string
    {
        return $this->getChildHtml('quote_items');
    }

    /**
     * Get payment html
     *
     * @return string
     */
    public function getPaymentHtml(): string
    {
        return $this->getChildHtml('quote_payment');
    }

    /**
     * View URL getter
     *
     * @param int $quoteId
     *
     * @return string
     */
    public function getViewUrl(int $quoteId): string
    {
        return $this->getUrl('sales_quote/*/*', ['quote_id' => $quoteId]);
    }

    /**
     * ######################## TAB settings #################################
     */

    /**
     * @return mixed
     */
    public function getTabLabel()
    {
        return __('Information');
    }

    /**
     * @return mixed
     */
    public function getTabTitle()
    {
        return __('Quote Information');
    }

    /**
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return false
     */
    public function isHidden()
    {
        return false;
    }
}
