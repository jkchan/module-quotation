<?php
namespace Sales\Quote\Controller\Adminhtml\Quote;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\QuoteRepository;
use Sales\Quote\Model\Quote\AddressRepository;

class Address extends Action
{
    /**
     * @param Context           $context
     * @param Registry          $registry
     * @param PageFactory       $resultPageFactory
     * @param AddressRepository $addressRepository
     * @param QuoteRepository   $quoteRepository
     */
    public function __construct(
        Context $context,
        protected Registry $registry,
        protected PageFactory $resultPageFactory,
        protected AddressRepository $addressRepository,
        protected QuoteRepository $quoteRepository
    ) {
        parent::__construct($context);
    }

    /**
     * Edit order address form
     *
     * @return Redirect|Page
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $addressId = (int)$this->getRequest()->getParam('address_id');
        $address   = $this->addressRepository->get($addressId);
        if ($address->getId()) {
            $quote = $this->quoteRepository->get($address->getQuoteId());
            $address->setQuote($quote);
            $this->registry->register('quote_address', $address);

            return $this->resultPageFactory->create();
        } else {
            return $this->resultRedirectFactory->create()->setPath('sales/*/');
        }
    }
}
