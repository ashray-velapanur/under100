<?php

class GaussDev_Deals_Model_Observer
{

    public function cleanCache(Varien_Event_Observer $observer)
    {
        if ($observer->getType() == 'categoryImage') {
            $cacheDir = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category' . DS . 'cache';
            mageDelTree($cacheDir);
        }

        return $this;
    }
}