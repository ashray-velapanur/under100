<?php

class Beagles_Brands_Model_Resource_Brands_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('brands/brands');
    }
}