<?php
namespace Sales\Quote\Helper\Tax;

use Magento\Tax\Helper\Data as MagentoTaxData;
class Data extends MagentoTaxData
{
    /**
     * @param  \Magento\Sales\Model\EntityInterface $current
     * @return array
     */
    protected function calculateTaxForOrder($current): array
    {
        $taxClassAmount = [];

        $orderTaxDetails = $this->orderTaxManagement->getOrderTaxDetails($current->getId());
        $appliedTaxes = $orderTaxDetails->getAppliedTaxes();
        foreach ($appliedTaxes as $appliedTax) {
            $taxCode = $appliedTax->getCode();
            $taxClassAmount[$taxCode]['tax_amount'] = $appliedTax->getAmount();
            $taxClassAmount[$taxCode]['base_tax_amount'] = $appliedTax->getBaseAmount();
            $taxClassAmount[$taxCode]['title'] = $appliedTax->getTitle();
            $taxClassAmount[$taxCode]['percent'] = $appliedTax->getPercent();
        }

        return $taxClassAmount;
    }
}