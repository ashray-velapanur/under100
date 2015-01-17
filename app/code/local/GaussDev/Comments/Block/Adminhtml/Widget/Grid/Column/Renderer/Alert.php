<?php

class GaussDev_Comments_Block_Adminhtml_Widget_Grid_Column_Renderer_Alert
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $truncateLength = 200;

        $value = parent::_getValue($row);
        $remainder = '';
        $text = Mage::helper('core/string')->truncate($value, $truncateLength, '[...]', $remainder);
        if ($this->getColumn()->getEscape()) {
            $text = $this->escapeHtml($text);
        }
        if ($this->getColumn()->getNl2br()) {
            $text = nl2br($text);
        }
        $full = json_encode($value);
        $onclickFunction = 'alert';
        $onclick = strlen($remainder) ? "onclick='{$onclickFunction}({$full})'" : '';
        $cursor = strlen($remainder) ? "style='cursor: pointer;'" : "style='cursor: default;'";
        $text = "<span {$cursor} {$onclick}>{$text}</span>";

        return $text;
    }
}