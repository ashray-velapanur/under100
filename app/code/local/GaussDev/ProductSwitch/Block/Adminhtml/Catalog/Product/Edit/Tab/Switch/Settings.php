<?php

class GaussDev_ProductSwitch_Block_Adminhtml_Catalog_Product_Edit_Tab_Switch_Settings
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareLayout()
    {
        $this->setChild('continue_button', $this->getLayout()->createBlock('adminhtml/widget_button')->setData(array(
            'label'   => $this->__('Continue'),
            'onclick' => "setSettings('" . $this->getContinueUrl() . "','attribute_set_id','product_type')",
            'class'   => 'save'
        )));

        return parent::_prepareLayout();
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset('settings', array('legend' => $this->__('Switch Type or Attribute Set')));

        $entityType = Mage::registry('product')->getResource()->getEntityType();

        $fieldset->addField('attribute_set_id', 'select', array(
            'label'  => $this->__('Attribute Set'),
            'title'  => $this->__('Attribute Set'),
            'name'   => 'set',
            'value'  => $entityType->getDefaultAttributeSetId(),
            'values' => array('' => '') + Mage::getResourceModel('eav/entity_attribute_set_collection')
                                              ->setEntityTypeFilter($entityType->getId())
                                              ->load()
                                              ->toOptionArray()
        ));

        $fieldset->addField('product_type', 'select', array(
            'label'  => $this->__('Product Type'),
            'title'  => $this->__('Product Type'),
            'name'   => 'type',
            'value'  => '',
            'values' => array('' => '') + Mage::getModel('catalog/product_type')->getOptionArray()
        ));

        $fieldset->addField('continue_button', 'note', array(
            'text' => $this->getChildHtml('continue_button'),
        ));

        $this->setForm($form);
    }

    public function getContinueUrl()
    {
        return $this->getUrl('*/*/productswitch', array(
            '_current' => true,
            'set'      => '{{attribute_set}}',
            'type'     => '{{type}}'
        ));
    }
}
