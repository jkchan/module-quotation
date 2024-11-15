<?php
namespace Sales\Quote\Block\Adminhtml\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Action;
use Magento\Framework\DataObject;

class QuoteAction extends Action
{
    /**
     * Renders column
     *
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row): string
    {
        if (!$row->getIsActive()) {
            return '&nbsp;';
        }

        $actions = $this->getColumn()->getActions();
        if (empty($actions) || !is_array($actions)) {
            return '&nbsp;';
        }

        if (count($actions) == 1 && !$this->getColumn()->getNoLink()) {
            foreach ($actions as $action) {
                if (is_array($action)) {
                    return $this->_toLinkHtml($action, $row);
                }
            }
        }

        $out = '<select class="admin__control-select" onchange="varienGridAction.execute(this);">' .
            '<option value=""></option>';
        $i = 0;
        foreach ($actions as $action) {
            $i++;
            if (is_array($action)) {
                $out .= $this->_toOptionHtml($action, $row);
            }
        }
        $out .= '</select>';
        return $out;
    }
}
