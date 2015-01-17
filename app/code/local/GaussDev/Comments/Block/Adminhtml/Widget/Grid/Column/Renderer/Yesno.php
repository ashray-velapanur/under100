<?php

class GaussDev_Comments_Block_Adminhtml_Widget_Grid_Column_Renderer_Yesno
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = (bool)$row->getData($this->getColumn()->getIndex());

        return $value ? '<b>Yes</b>' : 'No';

    }
}