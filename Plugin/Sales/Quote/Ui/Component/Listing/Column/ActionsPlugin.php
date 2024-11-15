<?php
namespace Sales\Quote\Plugin\Sales\Quote\Ui\Component\Listing\Column;

use Sales\Quote\Model\Admin\Acl;
use Sales\Quote\Ui\Component\Listing\Column\Actions;

class ActionsPlugin
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
     * @param Actions $subject
     * @param void $result
     * @return void
     */
    public function afterPrepare(Actions $subject, $result)
    {
        $isAllowViewQuote = $this->adminAcl->isAllowViewQuote();
        $isAllowCreateOrder = $this->adminAcl->isAllowCreateOrder();
        $config = $subject->getData('config');
        $config['componentDisabled'] = !$isAllowViewQuote && !$isAllowCreateOrder;
        $config['isAllowViewQuote'] = $isAllowViewQuote;
        $config['isAllowCreateOrder'] = $isAllowCreateOrder;
        $subject->setData('config', $config);
        return $result;
    }

    /**
     * @param Actions $subject
     * @param array $result
     * @return array
     */
    public function afterPrepareDataSource(Actions $subject, $result)
    {
        if (!isset($result['data']['items'])) {
            return $result;
        }
        $config = $subject->getData('config');
        $isAllowViewQuote = $config['isAllowViewQuote'] ?? false;
        $isAllowCreateOrder = $config['isAllowCreateOrder'] ?? false;
        if (($isAllowViewQuote && $isAllowCreateOrder) || (!$isAllowViewQuote && !$isAllowCreateOrder)) {
            return $result;
        }
        foreach ($result['data']['items'] as &$item) {
            $columnName = $subject->getData('name');
            if (!$isAllowViewQuote) {
                unset($item[$columnName]['view_edit']);
            }
            if (!$isAllowCreateOrder) {
                unset($item[$columnName]['create_order']);
                unset($item[$columnName]['force_create_order']);
            }
        }
        return $result;
    }
}
