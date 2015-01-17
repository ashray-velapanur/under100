<?php

class GaussDev_Deals_Model_Catalog_Category extends Mage_Catalog_Model_Category
{
    public function getDealImageUrl($width = null, $height = null)
    {
        $originalImage = $this->getData('deal_image') ?
            Mage::getBaseUrl('media') . 'catalog/category/' . $this->getData('deal_image') : null;

        if ($width || $height) {
            $url = Mage::helper('gaussdev_deals/image')->init($originalImage)->resize($width, $height);

            return $url;
        } else {
            return $originalImage ?: Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/image.jpg');
        }

    }

    public function getDealMobileImageUrl($width = null, $height = null)
    {
        $originalImage = $this->getData('deal_mobile_image') ?
            Mage::getBaseUrl('media') . 'catalog/category/' . $this->getData('deal_mobile_image') : null;

        if ($width || $height) {
            $url = Mage::helper('gaussdev_deals/image')->init($originalImage)->resize($width, $height);

            return $url;
        } else {
            return $originalImage ?: Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/image.jpg');
        }

    }
}