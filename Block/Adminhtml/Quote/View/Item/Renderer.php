<?php
namespace Sales\Quote\Block\Adminhtml\Quote\View\Item;

use Magento\Catalog\Helper\Product\Configuration;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemResolverInterface;
use Magento\Catalog\Pricing\Price\ConfiguredPriceInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class Renderer extends Template implements IdentityInterface
{
    /**
     * Whether qty will be converted to number
     */
    protected $strictQtyMode = true;

    /**
     * Initializing Product URL to empty string
     */
    protected $productUrl    = '';

    /**
     * Check, whether product URL rendering should be ignored
     */
    protected $ignoreProductUrl = false;

    /** @var AbstractItem */
    protected $item;


    /**
     * @param Context                         $context
     * @param Configuration                   $productConfig
     * @param Data                            $urlHelper
     * @param ManagerInterface                $messageManager
     * @param PriceCurrencyInterface          $priceCurrency
     * @param InterpretationStrategyInterface $messageInterpretationStrategy
     * @param array                           $data
     * @param ItemResolverInterface|null      $itemResolver
     *
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        protected Configuration $productConfig,
        protected Data $urlHelper,
        protected ManagerInterface $messageManager,
        protected PriceCurrencyInterface $priceCurrency,
        protected InterpretationStrategyInterface $messageInterpretationStrategy,
        protected ItemResolverInterface $itemResolver,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
        $this->itemResolver    = $itemResolver ?: ObjectManager::getInstance()->get(ItemResolverInterface::class);
    }

    /**
     * Set item for render
     *
     * @param AbstractItem $item
     *
     * @return Renderer
     */
    public function setItem(AbstractItem $item): Renderer
    {
        $this->item = $item;

        return $this;
    }

    /**
     * Get quote item
     *
     * @return AbstractItem
     */
    public function getItem(): AbstractItem
    {
        return $this->item;
    }

    /**
     * Get item product
     *
     * @return Product
     */
    public function getProduct(): Product
    {
        return $this->getItem()->getProduct();
    }

    /**
     * Identify the product from which thumbnail should be taken.
     *
     * @return Product
     */
    public function getProductForThumbnail(): Product
    {
        return $this->itemResolver->getFinalProduct($this->getItem());
    }


    /**
     * Check Product has URL
     *
     * @return bool
     */
    public function hasProductUrl(): bool
    {
        if ($this->ignoreProductUrl) {
            return false;
        }

        if ($this->productUrl || $this->getItem()->getRedirectUrl()) {
            return true;
        }

        $product = $this->getProduct();
        $option  = $this->getItem()->getOptionByCode('product_type');
        if ($option) {
            $product = $option->getProduct();
        }

        if ($product->isVisibleInSiteVisibility()) {
            return true;
        } else {
            if ($product->hasUrlDataObject()) {
                $data = $product->getUrlDataObject();
                if (in_array($data->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Retrieve URL to item Product
     *
     * @return string
     */
    public function getProductUrl(): string
    {
        if ($this->productUrl !== null) {
            return $this->productUrl;
        }
        if ($this->getItem()->getRedirectUrl()) {
            return $this->getItem()->getRedirectUrl();
        }

        $product = $this->getProduct();
        $option  = $this->getItem()->getOptionByCode('product_type');
        if ($option) {
            $product = $option->getProduct();
        }

        return $product->getUrlModel()->getUrl($product);
    }

    /**
     * Get item product name
     *
     * @return string
     */
    public function getProductName(): string
    {
        if ($this->hasProductName()) {
            return $this->getData('product_name');
        }

        return $this->getProduct()->getName();
    }

    public function getEditUrl(): string
    {
        $product = $this->getProduct();

        return $this->_urlBuilder->getUrl('catalog/product/edit', ['id' => $product->getId()]);
    }

    /**
     * Get product customize options
     *
     * @return array
     */
    public function getProductOptions(): array
    {
        /* @var $helper Configuration */
        $helper = $this->productConfig;

        return $helper->getCustomOptions($this->getItem());
    }

    /**
     * Get list of all options for product
     *
     * @return array
     */
    public function getOptionList(): array
    {
        return $this->getProductOptions();
    }

    /**
     * Get quote item qty
     *
     * @return float|int
     */
    public function getQty()
    {
        if (!$this->strictQtyMode && (string)$this->getItem()->getQty() == '') {
            return '';
        }

        return $this->getItem()->getQty();
    }

    /**
     * Retrieve item messages, return array with keys, text => the message text, type => type of a message
     *
     * @return array
     */
    public function getMessages(): array
    {
        $messages  = [];
        $quoteItem = $this->getItem();

        // Add basic messages occurring during this page load
        $baseMessages = $quoteItem->getMessage(false);
        if ($baseMessages) {
            foreach ($baseMessages as $message) {
                $messages[] = ['text' => $message, 'type' => $quoteItem->getHasError() ? 'error' : 'notice'];
            }
        }

        /* @var $collection \Magento\Framework\Message\Collection */
        $collection = $this->messageManager->getMessages(true, 'quoteitem' . $quoteItem->getId());
        if ($collection) {
            $additionalMessages = $collection->getItems();
            foreach ($additionalMessages as $message) {
                /* @var $message \Magento\Framework\Message\MessageInterface */
                $messages[] = [
                    'text' => $this->messageInterpretationStrategy->interpret($message),
                    'type' => $message->getType(),
                ];
            }
        }
        $this->messageManager->getMessages(true, 'quoteitem' . $quoteItem->getId())->clear();

        return $messages;
    }

    /**
     * Accept option value and return its formatted view
     *
     * @param string|array $optionValue
     *              Method works well with these $optionValue format:
     *              1. String
     *              2. Indexed array e.g. array(val1, val2, ...)
     *              3. Associative array, containing additional option info, including option value, e.g.
     *              array
     *              (
     *              [label] => ...,
     *              [value] => ...,
     *              [print_value] => ...,
     *              [option_id] => ...,
     *              [option_type] => ...,
     *              [custom_view] =>...,
     *              )
     *
     * @return array
     */
    public function getFormattedOptionValue($optionValue): array
    {
        /* @var $helper Configuration */
        $helper = $this->productConfig;
        $params = [
            'max_length'   => 55,
            'cut_replacer' => ' <a href="#" class="dots tooltip toggle" onclick="return false">...</a>',
        ];

        return $helper->getFormattedOptionValue($optionValue, $params);
    }

    /**
     * Return product additional information block
     *
     * @return bool|BlockInterface
     * @throws LocalizedException
     */
    public function getProductAdditionalInformationBlock()
    {
        return $this->getLayout()->getBlock('additional.product.info');
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getItem()) {
            $identities = $this->getProduct()->getIdentities();
        }

        return $identities;
    }

    /**
     * Get product price formatted with html (final price, special price, mrp price)
     *
     * @param Product $product
     *
     * @return string
     * @throws LocalizedException
     */
    public function getProductPriceHtml(Product $product): string
    {
        $priceRender = $this->getPriceRender();
        $priceRender->setItem($this->getItem());

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                ConfiguredPriceInterface::CONFIGURED_PRICE_CODE,
                $product,
                [
                    'include_container'     => true,
                    'display_minimal_price' => true,
                    'zone'                  => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                ]
            );
        }

        return $price;
    }

    /**
     * Get price renderer.
     *
     * @return BlockInterface|bool
     * @throws LocalizedException
     */
    protected function getPriceRender()
    {
        return $this->getLayout()->getBlock('product.price.render.default');
    }

    /**
     * Convert prices for template
     *
     * @param float $amount
     * @param bool  $format
     *
     * @return float
     */
    public function convertPrice($amount, $format = false)
    {
        return $format
            ? $this->priceCurrency->convertAndFormat($amount)
            : $this->priceCurrency->convert($amount);
    }

    /**
     * Return the unit price html
     *
     * @param AbstractItem $item
     *
     * @return string
     * @throws LocalizedException
     */
    public function getUnitPriceHtml(AbstractItem $item): string
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('quote.item.price.unit');
        $block->setItem($item);

        return $block->toHtml();
    }

    /**
     * Return row total html
     *
     * @param AbstractItem $item
     *
     * @return string
     * @throws LocalizedException
     */
    public function getRowTotalHtml(AbstractItem $item): string
    {
        /** @var Renderer $block */
        $block = $this->getLayout()->getBlock('quote.item.price.row');
        $block->setItem($item);

        return $block->toHtml();
    }
}
