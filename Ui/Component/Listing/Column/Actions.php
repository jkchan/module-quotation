<?php
namespace Sales\Quote\Ui\Component\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{
    /**
     * Actions constructor.
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        protected UrlInterface $urlBuilder,
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
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')]['view_edit'] = [
                'href' => $this->urlBuilder->getUrl(
                    'sales_quote/quote/view',
                    ['quote_id' => $item['entity_id']]
                ),
                'label' => 'View/Edit',
                'hidden' => false
            ];

            $actions = $this->getData('action_list');
            foreach ($actions as $key => $action) {
                $actionLabel = $action['label'];
                $params = $action['params'];
                foreach ($params as $field => $param) {
                    $params[$field] = $item[$param];
                }
                $item[$this->getData('name')][$key] = [
                    'href' => $this->urlBuilder->getUrl(
                        'sales_quote/order/create',
                        [
                            'quote_id' => $item[$action['params']['id']],
                            'force' => $key == 'force_create_order',
                        ]
                    ),
                    'label' => $actionLabel,
                    'hidden' => false,
                ];
            }
        }

        return $dataSource;
    }
}
