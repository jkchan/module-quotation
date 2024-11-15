<?php
namespace Sales\Quote\Model;

use Magento\Quote\Model\ResourceModel\Quote\Collection;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class DataProvider extends AbstractDataProvider
{
    const QUOTE_FIELDS = [
        'entity_id',
        'is_active',
        'customer_email',
        'store_id',
        'grand_total',
        'customer_firstname',
        'customer_lastname',
    ];

    /** @var Collection */
    protected $collection;

    /**
     * /**
     * @param string            $name
     * @param string            $primaryFieldName
     * @param string            $requestFieldName
     * @param CollectionFactory $quoteCollectionFactory
     * @param array             $meta
     * @param array             $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $quoteCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $quoteCollectionFactory->create();
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $this->loadedData = [];
        $quotes = $this->getQuoteCollection();
        foreach ($quotes as $quote) {
            $this->loadedData[$quote->getId()] = $quote->getData();
        }

        return $this->loadedData;
    }

    /**
     * @return Collection
     */
    protected function getQuoteCollection(): Collection
    {
        return $this->collection->addFieldToSelect(self::QUOTE_FIELDS);
    }
}
