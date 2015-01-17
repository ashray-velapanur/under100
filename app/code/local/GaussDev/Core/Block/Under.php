<?php

class GaussDev_Core_Block_Under extends Mage_Core_Block_Template
{
    public function productHtml(Mage_Catalog_Model_Product $product, $excludeOverlay = false)
    {
        $this->setData('product', $product);
        $this->setData('exclude_overlay', $excludeOverlay);
        $this->setTemplate('under/product.phtml');

        return $this->toHtml();
    }
}