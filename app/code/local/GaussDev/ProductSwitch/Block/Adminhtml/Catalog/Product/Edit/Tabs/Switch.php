<?php

class GaussDev_ProductSwitch_Block_Adminhtml_Catalog_Product_Edit_Tabs_Switch
    extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
{
    protected function _prepareLayout()
    {
        $this->addTab('super_settings', array(
            'label'   => $this->__('Switch Type or Attribute Set'),
            'content' => $this->getLayout()
                              ->createBlock('gaussdev_productswitch/adminhtml_catalog_product_edit_tab_switch_settings')
                              ->toHtml(),
            'active'  => true
        ));
    }
}

