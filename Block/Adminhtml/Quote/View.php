<?php
namespace Sales\Quote\Block\Adminhtml\Quote;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Quote\Model\Quote;

class View extends Container
{
    /**
     * Block group
     *
     * @var string
     */
    protected $_blockGroup = 'Sales_Quote';

    /**
     * @param Context  $context
     * @param Registry $registry
     * @param array    $data
     */
    public function __construct(
        Context $context,
        protected Registry $coreRegistry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Constructor
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _construct()
    {
        $this->_objectId   = 'quote_id';
        $this->_controller = 'adminhtml_quote';
        $this->_mode       = 'view';

        parent::_construct();

        $this->removeButton('delete');
        $this->removeButton('reset');
        $this->removeButton('save');
        $this->setId('sales_quote_view');
        $quote = $this->getQuote();
        $this->addButton(
            'create_order',
            [
                'label'   => __('Create Order'),
                'id'      => 'create-order-button',
                'class'   => 'primary',
                'onclick' => sprintf(
                    "confirmSetLocation('%s', '%s')",
                    'You gonna create Order. Quote will be deactivated. Proceed?',
                    $this->getCreateOrderUrl((int)$quote->getId())
                ),
            ]
        );

        $this->addButton(
            'create_order_force',
            [
                'label'   => __('Force Create Order'),
                'id'      => 'create-order-force-button',
                'class'   => 'primary',
                'onclick' => sprintf(
                    "confirmSetLocation('%s', '%s')",
                    'You gonna force creating Order. Admin and Store data will be used for Shipping & Billing Info. Proceed?',
                    $this->getCreateOrderUrl((int)$quote->getId(), true)
                ),
            ]
        );

        $this->addButton(
            'save',
            [
                'label' => __('Save'),
                'class' => 'save primary',
                'data_attribute' => [
                    'mage-init' => ['button' => ['event' => 'save', 'target' => '#edit_form']],
                ]
            ],
            1
        );

        if (!$quote) {
            return;
        }
    }

    /**
     * Retrieve quote model object
     *
     * @return Quote
     */
    public function getQuote(): Quote
    {
        return $this->coreRegistry->registry('quote');
    }

    /**
     * Retrieve Quote Identifier
     *
     * @return int
     */
    public function getQuoteId(): ?int
    {
        return $this->getQuote() ? (int)$this->getQuote()->getId() : null;
    }

    /**
     * Get header text
     *
     * @return Phrase
     */
    public function getHeaderText(): Phrase
    {
        return __('Quote #%1', $this->getQuoteId());
    }

    /**
     * URL getter
     *
     * @param string $params
     * @param array  $params2
     *
     * @return string
     */
    public function getUrl($params = '', $params2 = []): ?string
    {
        $params2['quote_id'] = $this->getQuoteId();

        return parent::getUrl($params, $params2);
    }

    /**
     * @return string
     */
    public function getBackUrl(): string
    {
        if ($this->getQuote() && $this->getQuote()->getBackUrl()) {
            return $this->getQuote()->getBackUrl();
        }

        return $this->getUrl('sales_quote/*/index');
    }

    /**
     * @param int  $quoteId
     * @param bool $force
     *
     * @return string
     */
    protected function getCreateOrderUrl(int $quoteId, bool $force = false): string
    {
        return $this->getUrl('sales_quote/order/create', ['quote_id' => $quoteId, 'force' => (int)$force]);
    }
}
