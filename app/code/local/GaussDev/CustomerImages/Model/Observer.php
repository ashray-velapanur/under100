<?php

class GaussDev_CustomerImages_Model_Observer
{

    public function cleanCache(Varien_Event_Observer $observer)
    {
        if ($observer->getType() == 'customerImage') {
            $cacheDir = Mage::getBaseDir('media') . DS . 'customer' . DS . 'cache';
            mageDelTree($cacheDir);
        }

        return $this;
    }
}