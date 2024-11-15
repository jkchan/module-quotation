<?php
namespace Sales\Quote\Cron;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\ResourceModel\Quote\Item\Collection;
use Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class RemoveDisableItems
 */
class RemoveDisableItems
{
    public const SECONDS_IN_DAY = 86400;
    public const CLEAN_PERIOD_IN_DAYS = 30; //last month

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param CollectionFactory $quoteItemCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CollectionFactory $quoteItemCollectionFactory,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Remove items with disabled products (cron process)
     *
     * @return void
     */
    public function execute()
    {
        /** @var Item $item */
        $n = 1;     //Coefficient for debug. Should be 1 in production mode
        $rangeDays = self::CLEAN_PERIOD_IN_DAYS * self::SECONDS_IN_DAY;
        $fromDays = $rangeDays * $n;
        $toDays = $rangeDays * ($n - 1);
        $dateFrom = date("Y-m-d", time() - $fromDays);
        /** @var Collection $itemsCollection */
        $itemsCollection = $this->quoteItemCollectionFactory->create();
        $itemsCollection->addFieldToSelect('item_id');
        $itemsCollection->addFieldToSelect('product_id');
        $itemsCollection->addFieldToFilter('updated_at', ['from' => $dateFrom]);
        if (!empty($toDays)) {
            $dateTo = date("Y-m-d", time() - $toDays);
            $itemsCollection->addFieldToFilter('updated_at', ['to' => $dateTo]);
        }

        $removedItems = 0;
        try {
            foreach ($itemsCollection as $item) {
                $product = $this->productRepository->getById($item->getProductId());
                if ($product->getStatus() == ProductStatus::STATUS_DISABLED) {
                    $item->delete();
                    $removedItems++;
                }
            }
        } catch (Exception $exception) {
            $this->logger->critical('Error during removing quote items. ' . $exception);
        }
    }
}
