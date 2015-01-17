<?php

class GaussDev_ProductSwitch_Model_Observer
{

    public function addSwitchButton()
    {
        $layout = Mage::app()->getLayout();

        $productEditBlock = $layout->getBlock('product_edit');

        $resetButton = $productEditBlock->getChild('reset_button');

        $myButton = $layout->createBlock('gaussdev_productswitch/adminhtml_widget_button');

        $container = $layout->createBlock('core/text_list', 'button_container');

        $container->append($resetButton);
        $container->append($myButton);

        $productEditBlock->setChild('reset_button', $container);
    }

    public function addMassAction($observer)
    {
        $block = $observer->getEvent()->getBlock();
        if (get_class($block) == 'Mage_Adminhtml_Block_Widget_Grid_Massaction'
            && $block->getRequest()->getControllerName() == 'catalog_product'
            && !Mage::getStoreConfig('advanced/modules_disable_output/GaussDev_ProductSwitch')
        ) {

            $entityType = Mage::getModel('catalog/product')->getResource()->getEntityType();
            $block->addItem('productswitch', array(
                'label'      => Mage::helper('core')->__('Switch Type or Attribute Set'),
                'url'        => Mage::getUrl('*/*/switchMass'),
                'additional' => array(
                    'attrset'  => array(
                        'name'   => 'set',
                        'type'   => 'select',
                        'label'  => Mage::helper('core')->__('Attribute Set'),
                        'values' => array('' => '') + Mage::getResourceModel('eav/entity_attribute_set_collection')
                                                          ->setEntityTypeFilter($entityType->getId())
                                                          ->load()
                                                          ->toOptionArray()
                    ),
                    'prodtype' => array(
                        'name'   => 'type',
                        'type'   => 'select',
                        'label'  => Mage::helper('core')->__('Product Type'),
                        'values' => array('' => '') + Mage::getModel('catalog/product_type')->getOptionArray()
                    )
                )
            ));
        }
    }

}