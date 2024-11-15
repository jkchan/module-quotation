<?php
namespace Sales\Quote\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote\PaymentFactory;
use Sales\Quote\Model\Quote\Manager;

class Create extends Action
{
    /**
     * Create constructor.
     *
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     * @param Manager     $manager
     */
    public function __construct(
        protected Context $context,
        protected PageFactory $resultPageFactory,
        protected Manager $manager
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $quoteId = (int)$this->getRequest()->getParam('quote_id');
        $force   = (bool)$this->getRequest()->getParam('force');
        if (!$quoteId) {
            $this->messageManager->addErrorMessage('No Such Quote.');

            return $this->_redirect('sales_quote/quote/index', ['_secure' => true]);
        }

        try {
            $orderId = $this->manager->prepareQuoteAndPlaceOrder($quoteId, $force);
            $this->messageManager->addComplexSuccessMessage(
                'orderCreatedSuccessfullyMessage',
                [
                    'url'      => $this->getUrl('sales/order/view', ['order_id' => $orderId]),
                    'order_id' => $orderId,
                ]
            );

            return $this->_redirect('sales/order/index', ['_secure' => true]);

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage('Order Creation Failed (Check Quote data): ');
            $this->messageManager->addExceptionMessage($e, $e->getMessage());

            return $this->_redirect('sales_quote/quote/view', ['quote_id' => $quoteId]);
        }
    }

    /**
     * @return boolean
     */
    protected function _isAllowed()
    {
        return true;
    }
}
