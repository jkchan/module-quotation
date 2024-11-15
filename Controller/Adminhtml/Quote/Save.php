<?php
namespace Sales\Quote\Controller\Adminhtml\Quote;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteRepository;

class Save extends Action
{
    /**
     * Allowed Fields Constant Value
     */
    const ALLOWED_FIELDS = [
        'customer_firstname',
        'customer_lastname',
        'customer_email',
    ];

    /**
     * InlineEdit constructor.
     *
     * @param Context         $context
     * @param PageFactory     $pageFactory
     * @param QuoteRepository $quoteRepository
     */
    public function __construct(
        Context $context,
        protected PageFactory $pageFactory,
        protected QuoteRepository $quoteRepository
    ) {
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $quoteId = $this->_request->getParam('quote_id');
        if (!$quoteId) {
            $this->messageManager->addErrorMessage(__('This quote no longer exists.'));
        }
        try {
            $quote = $this->quoteRepository->get($quoteId);
            if ($quote) {
                foreach (self::ALLOWED_FIELDS as $fieldKey) {
                    $quote->setData($fieldKey, $this->getRequest()->getParam($fieldKey));
                }
                $this->quoteRepository->save($quote);
                $this->messageManager->addSuccessMessage(__('Quote saved successfully.'));
            }
        } catch (\Exception $exception) {
            $this->messageManager->addErrorMessage(__('Error during saving quote. %1', $exception->getMessage()));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('sales_quote/*/view', ['quote_id' => $quoteId]);
    }
}
