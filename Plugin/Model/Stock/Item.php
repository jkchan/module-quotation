<?php
namespace Sales\Quote\Plugin\Model\Stock;

use Magento\Framework\App\RequestInterface;
use Magento\CatalogInventory\Model\Stock\Item as CoreItem;

/**
 * Class Item
 * @package Sales\Quote\Plugin\Model\Stock
 */
class Item
{
    /**
     * Status constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(
        protected RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @param CoreItem $subject
     * @param $result
     *
     * @return int|mixed
     * @SuppressWarnings(Unused)
     */
    public function afterGetBackorders(
        CoreItem $subject,
        $result
    ) {
        if ($this->request->getParam('type') === 'force_create_order') {
            return 1;
        }

        return $result;
    }
}
