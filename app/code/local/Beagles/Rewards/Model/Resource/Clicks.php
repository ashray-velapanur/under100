<?php
class Beagles_Rewards_Model_Resource_Clicks extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('rewards/clicks', 'pid');
    }
}