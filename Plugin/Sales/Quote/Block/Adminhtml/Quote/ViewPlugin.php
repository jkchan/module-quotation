<?php
namespace Sales\Quote\Plugin\Sales\Quote\Block\Adminhtml\Quote;

use Sales\Quote\Model\Admin\Acl;
use Sales\Quote\Block\Adminhtml\Quote\View;

class ViewPlugin
{
    /**
     * @param Acl $adminAcl
     */
    public function __construct(
        protected Acl $adminAcl
    ) {
        $this->adminAcl = $adminAcl;
    }

    public function beforeSetLayout(
        View $subject
    ) {
        if ($this->adminAcl->isAllowCreateOrder()) {
            return null;
        }
        $subject->removeButton('create_order');
        $subject->removeButton('create_order_force');
    }
}
