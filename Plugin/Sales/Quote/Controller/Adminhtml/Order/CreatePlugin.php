<?php
namespace Sales\Quote\Plugin\Sales\Quote\Controller\Adminhtml\Order;

use Sales\Quote\Model\Admin\Acl;
use Sales\Quote\Controller\Adminhtml\Order\Create;
use Magento\Framework\App\ResponseInterface;

class CreatePlugin
{
    /**
     * @param Acl $adminAcl
     */
    public function __construct(
        protected Acl $adminAcl
    ) {
        $this->adminAcl = $adminAcl;
    }

    /**
     * @param \Sales\Quote\Controller\Adminhtml\Order\Create $subject
     * @param \Magento\Framework\App\ResponseInterface $result
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function afterExecute(
        Create $subject,
        $result
    ) {
        if (!($result instanceof ResponseInterface)) {
            return $result;
        }
        $location = $result->getHeader('Location');
        if (strpos($location, 'sales_quote/quote/view') === false) {
            return $result;
        }
        if (!$this->adminAcl->isAllowViewQuote()) {
            $result->setRedirect($subject->getUrl('sales_quote/quote/index', ['_secure' => true]));
        }
        return $result;
    }
}
