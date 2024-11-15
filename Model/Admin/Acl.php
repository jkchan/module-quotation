<?php
namespace Sales\Quote\Model\Admin;

use Magento\Framework\AuthorizationInterface;

class Acl
{
    /**
     * @param AuthorizationInterface $authorization
     */
    public function __construct(
        protected AuthorizationInterface $authorization
    ) {
        $this->authorization = $authorization;
    }

    /**
     * @return mixed
     */
    public function isAllowViewQuote()
    {
        return $this->authorization->isAllowed('Sales_Quote::view_quote');
    }

    /**
     * @return mixed
     */
    public function isAllowCreateOrder()
    {
        return $this->authorization->isAllowed('Sales_Quote::create_order');
    }
}
