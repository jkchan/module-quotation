<?php
namespace Sales\Quote\Block\Adminhtml\Quote;

use Magento\Directory\Model\Currency;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote;
use Magento\Sales\Helper\Admin;
use Magento\Sales\Block\Adminhtml\Totals as MagentoSalesTotals;

class Totals extends MagentoSalesTotals
{
    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @param Context         $context
     * @param Registry        $registry
     * @param Admin           $adminHelper
     * @param CurrencyFactory $currencyFactory
     * @param array           $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Admin $adminHelper,
        protected CurrencyFactory $currencyFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $adminHelper, $data);
        $this->currencyFactory = $currencyFactory;
        $this->currency        = $this->currencyFactory->create();
        $this->currency->load($this->getSource()->getQuoteCurrencyCode());
    }

    /**
     * @return Quote|mixed|null
     */
    public function getSource()
    {
        return $this->_coreRegistry->registry('quote');
    }

    /**
     * Format total value based on order currency
     *
     * @param DataObject $total
     *
     * @return string
     */
    public function formatValue($total): ?string
    {
        if (!$total->getIsFormated()) {
            return $this->formatPricePrecision((float)$total->getValue(), 2);
        }

        return $total->getValue();
    }

    /**
     * Get currency model instance. Will be used currency with which order placed
     *
     * @return Currency
     */
    public function getQuoteCurrency(): Currency
    {
        return $this->currency;
    }

    /**
     * Get formatted price value including order currency rate to order website currency
     *
     * @param float $price
     * @param bool  $addBrackets
     *
     * @return string
     */
    public function formatPrice(float $price, bool $addBrackets = false): string
    {
        return $this->formatPricePrecision($price, 2, $addBrackets);
    }

    /**
     * Format price precision
     *
     * @param float $price
     * @param int   $precision
     * @param bool  $addBrackets
     *
     * @return string
     */
    public function formatPricePrecision(float $price, int $precision, bool $addBrackets = false): string
    {
        return $this->getQuoteCurrency()->formatPrecision($price, $precision, [], true, $addBrackets);
    }
}
