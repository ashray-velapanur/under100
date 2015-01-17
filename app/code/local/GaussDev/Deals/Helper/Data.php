<?php

class GaussDev_Deals_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getCollection()
    {
        /** @var Mage_Catalog_Model_Resource_Category_Collection $dealsCollection */
        $children = explode(',', Mage::getSingleton('catalog/category')->load(37)->getChildren());
        $collection = Mage::getResourceModel('catalog/category_collection')
                          ->addIsActiveFilter()
                          ->addIdFilter($children)
                          ->addAttributeToSort('position')
                          ->addAttributeToSelect('*');

        return $collection;
    }


}