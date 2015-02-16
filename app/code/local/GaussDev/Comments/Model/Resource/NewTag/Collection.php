<?php

class GaussDev_Comments_Model_Resource_Newtag_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('gaussdev_comments/newtag');
    }
}