<?php
namespace Sales\Quote\Plugin\Model\Stock;

use Magento\Framework\App\RequestInterface;
use Magento\CatalogInventory\Model\Stock\Status as CoreStatus;

/**
 * Class Status
 * @package Sales\Quote\Plugin\Model\Stock
 */
class Status
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
     * @param CoreStatus $subject
     * @param $result
     *
     * @return int|mixed
     * @SuppressWarnings(Unused)
     */
    public function afterGetStockStatus(
        CoreStatus $subject,
        $result
    ) {
        if ($this->request->getParam('type') === 'force_create_order') {
            return 1;
        }

        return $result;
    }
}
