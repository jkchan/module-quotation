<?php
namespace Sales\Quote\Block\Adminhtml\Quote\View;

use Magento\Backend\Block\Template;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\View\Element\Template as MagentoTemplate;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use RuntimeException;

class Items extends Template
{
    /**
     * Block alias fallback
     */
    const DEFAULT_TYPE = 'default';

    /**
     * @param Template\Context     $context
     * @param Registry             $registry
     * @param array                $data
     * @param JsonHelper|null      $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        Template\Context $context,
        protected Registry $registry,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * Get all cart items
     *
     * @return array
     */
    public function getItems(): array
    {
        return $this->getQuote()->getAllVisibleItems();
    }

    /**
     * Get item row html
     *
     * @param Item $item
     *
     * @return  string
     * @throws LocalizedException
     */
    public function getItemHtml(Item $item): string
    {
        $renderer = $this->getItemRenderer($item->getProductType())->setItem($item);

        return $renderer->toHtml();
    }

    public function getQuote(): ?Quote
    {
        return $this->registry->registry('quote');
    }

    /**
     * Retrieve renderer list
     *
     * @return bool|AbstractBlock|BlockInterface
     * @throws LocalizedException
     */
    protected function _getRendererList()
    {
        return $this->getRendererListName()
            ? $this->getLayout()->getBlock(
                $this->getRendererListName()
            )
            : $this->getChildBlock(
                'renderer.list'
            );
    }

    /**
     * Retrieve item renderer block
     *
     * @param string|null $type
     *
     * @return MagentoTemplate
     * @throws RuntimeException|LocalizedException
     */
    public function getItemRenderer(string $type = ''): MagentoTemplate
    {
        if ($type === '') {
            $type = self::DEFAULT_TYPE;
        }
        $rendererList = $this->_getRendererList();
        if (!$rendererList) {
            throw new RuntimeException('Renderer list for block "' . $this->getNameInLayout() . '" is not defined');
        }

        return $rendererList->getRenderer($type, self::DEFAULT_TYPE, $this->getRendererTemplate());
    }

}
