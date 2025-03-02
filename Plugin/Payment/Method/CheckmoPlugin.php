<?php

namespace Sales\Quote\Plugin\Payment\Method;

use Magento\OfflinePayments\Model\Checkmo;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;

/**
 * Class CheckmoPlugin
 */
class CheckmoPlugin
{

    /**
     * @param State $appState
     */
    public function __construct(
        protected State $appState
    ) {}

    /**
     * Enable Check/Money Order Payment Method
     *
     * @param Checkmo $subject
     * @param $result
     * @return bool
     */
    public function afterIsActive(Checkmo $subject, $result)
    {
        if (!$result && $this->appState->getAreaCode() === Area::AREA_ADMINHTML) {
            $result = true;
        }

        return $result;
    }
}
