<?php
namespace Sales\Quote\Ui\Component\Listing\Column;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Quote\Model\QuoteRepository;
use Magento\Ui\Component\Listing\Columns\Column;
use Sales\Quote\Model\Quote\Address\Validator;

class IsValid extends Column
{

    /**
     * Actions constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param Validator          $validator
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        protected Validator $validator,
        protected QuoteRepository $quoteRepository,
        array $components = [],
        array $data = []
    ) {

        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     * @throws NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')] = $this->areAddressesValid($item['entity_id']);
        }

        return $dataSource;
    }

    /**
     * @param $quoteId
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    protected function areAddressesValid($quoteId): bool
    {
        $quote = $this->quoteRepository->get($quoteId);

        return $this->validator->validate($quote->getShippingAddress()) &&
            $this->validator->validate($quote->getBillingAddress());

    }
}
