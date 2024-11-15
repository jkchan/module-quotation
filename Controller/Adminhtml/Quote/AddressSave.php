<?php
namespace Sales\Quote\Controller\Adminhtml\Quote;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Quote\Model\Quote\AddressFactory;
use Magento\Quote\Model\QuoteRepository;
use Sales\Quote\Model\Quote\AddressRepository;

class AddressSave extends Action
{
    /**
     * @param Context           $context
     * @param Registry          $registry
     * @param PageFactory       $resultPageFactory
     * @param AddressRepository $addressRepository
     * @param AddressFactory    $addressFactory
     * @param RegionFactory     $regionFactory
     * @param QuoteRepository   $quoteRepository
     */
    public function __construct(
        Context $context,
        protected Registry $registry,
        protected PageFactory $resultPageFactory,
        protected AddressRepository $addressRepository,
        protected AddressFactory $addressFactory,
        protected RegionFactory $regionFactory,
        protected QuoteRepository $quoteRepository

    ) {
        parent::__construct($context);
    }

    /**
     * Save quote address
     *
     * @return Redirect
     */
    public function execute()
    {
        $addressId      = (int)$this->getRequest()->getParam('address_id');
        $address        = $this->addressRepository->get($addressId);
        $data           = $this->getRequest()->getPostValue();
        $data           = $this->updateRegionData($data);
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data && $address->getId()) {
            $address->addData($data);
            try {
                $this->addressRepository->save($address);
                $this->messageManager->addSuccessMessage(__('You updated the quote address.'));
                //Re-save for recalculating data
                $quote = $this->quoteRepository->get($address->getQuoteId());
                $this->quoteRepository->save($quote);

                return $resultRedirect->setPath('sales_quote/*/view', ['quote_id' => $address->getQuoteId()]);
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('We can\'t update the quote address right now.'));
            }

            return $resultRedirect->setPath('sales_quote/*/address', ['address_id' => $address->getId()]);
        } else {
            return $resultRedirect->setPath('sales_quote/*/view', ['quote_id' => $address->getQuoteId()]);
        }
    }

    /**
     * Update region data
     *
     * @param array $attributeValues
     *
     * @return array
     */
    protected function updateRegionData(array $attributeValues): array
    {
        if (!empty($attributeValues['region_id'])) {
            $newRegion                      = $this->regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region']      = $newRegion->getDefaultName();
        }

        return $attributeValues;
    }
}
