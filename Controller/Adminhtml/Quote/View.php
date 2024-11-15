<?php
namespace Sales\Quote\Controller\Adminhtml\Quote;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context as MagentoBackendContext;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\QuoteRepository;

class View extends Action
{
    /**
     * @param PageFactory           $resultPageFactory
     * @param QuoteRepository       $quoteRepository
     * @param Registry              $coreRegistry
     * @param MagentoBackendContext $context
     */
    public function __construct(
        protected PageFactory $resultPageFactory,
        protected QuoteRepository $quoteRepository,
        protected Registry $coreRegistry,
        MagentoBackendContext $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResultInterface|Page
     */
    public function execute()
    {
        $quote = $this->_initQuote();

        if ($quote) {
            $resultPage = $this->resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->prepend(__('View/Edit Quote #%1', $quote->getId()));

            return $resultPage;
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('sales_quote/*/');

        return $resultRedirect;
    }

    /**
     * Initialize quote model instance
     *
     * @return Quote|false
     */
    protected function _initQuote()
    {
        $id     = $this->getRequest()->getParam('quote_id');
        $result = false;
        try {
            $quote = $this->quoteRepository->get($id);
            if ($quote->getIsActive()) {
                $result = $quote;
                $this->coreRegistry->register('quote', $quote);
                $this->coreRegistry->register('current_quote', $quote);
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__($exception->getMessage()));
        }

        if (!$result) {
            $this->messageManager->addErrorMessage(__('This quote no longer exists.'));
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return $result;
    }
}
