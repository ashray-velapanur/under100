<?php

class Beagles_Rewards_Model_Resource_Clicks_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('rewards/clicks');
    }
}