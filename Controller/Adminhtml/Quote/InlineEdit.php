<?php
namespace Sales\Quote\Controller\Adminhtml\Quote;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResourceModel;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory;

/**
 * Class InlineEdit
 * @package Sales\Quote\Controller\Adminhtml\Quote
 */
class InlineEdit extends Action
{

    /**
     * InlineEdit constructor.
     *
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param QuoteResourceModel $quoteResourceModel
     * @param CollectionFactory $quoteCollection
     */
    public function __construct(
        Context $context,
        protected JsonFactory $jsonFactory,
        protected QuoteResourceModel $quoteResourceModel,
        protected CollectionFactory $quoteCollection
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->jsonFactory->create();
        $error      = false;
        $messages   = [];

        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data save.');
                $error      = true;
            } else {
                $collection = $this->quoteCollection->create()
                    ->addFieldToFilter('entity_id', array_keys($postItems));
                foreach ($collection as $quote) {
                    try {
                        $quote->setData(array_merge($quote->getData(), $postItems[$quote->getId()]));
                        $this->quoteResourceModel->save($quote);
                    } catch (Exception $e) {
                        $messages[] = "[Error : {$quote->getId()}]  {$e->getMessage()}";
                        $error      = true;
                    }
                }
            }
        }

        return $resultJson->setData(compact('messages', 'error'));
    }
}
