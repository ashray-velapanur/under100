<?php
class Beagles_Clicks_Model_Resource_Clicks extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('clicks/clicks', 'product_id');
    }
}