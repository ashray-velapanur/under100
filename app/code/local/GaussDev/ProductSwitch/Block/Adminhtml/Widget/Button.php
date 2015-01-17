<?php

class GaussDev_ProductSwitch_Block_Adminhtml_Widget_Button extends Mage_Adminhtml_Block_Widget_Button
{
    protected function _construct()
    {
        $product = Mage::registry('current_product');
        parent::_construct();
        $this->setData(array(
            'label'   => $this->__('Switch Type or Attribute Set'),
            'onclick' => 'setLocation(\'' . $this->getUrl('*/*/selectswitch', array('id' => $product->getId())) . '\')',
            'title'   => $this->__('Switch Type or Attribute Set')
        ));
    }
}