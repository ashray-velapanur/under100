<?php
class Beagles_Brands_Model_Resource_Brands extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('brands/brands', 'id');
    }
}