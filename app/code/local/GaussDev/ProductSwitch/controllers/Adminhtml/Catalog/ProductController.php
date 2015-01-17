<?php

class GaussDev_ProductSwitch_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Catalog_ProductController
{
    public function selectswitchAction()
    {
        $this->_initProduct();

        $this->_title($this->__('Switch Type or Attribute Set'));

        $this->loadLayout(array(
            'default',
            'adminhtml_catalog_product_edit',
            'gaussdev_switch'
        ));
        $this->_setActiveMenu('catalog/products');

        $this->renderLayout();
    }

    public function productswitchAction()
    {
        $product = $this->_initProduct();
        $setId = $this->getRequest()->getParam('set');
        $type = $this->getRequest()->getParam('type');
        if ($setId) {
            $product->setAttributeSetId($setId);
        }
        if ($type) {
            $product->setTypeId($type);
        }
        if ($product->hasDataChanges()) {
            $product->save();
        }
        $this->_redirect('*/*/edit', array(
            'id' => $product->getId(),
        ));
    }

    public function switchMassAction()
    {
        $products = $this->getRequest()->getPost('product');
        $setId = $this->getRequest()->getPost('set');
        $type = $this->getRequest()->getPost('type');
        foreach ($products as $id) {
            $product = Mage::getModel('catalog/product')->load($id);
            if ($setId) {
                $product->setAttributeSetId($setId);
            }
            if ($type) {
                $product->setTypeId($type);
            }
            if ($product->hasDataChanges()) {
                $product->save();
            }
            unset($product);
        }
        $this->_redirectReferer();

    }
}