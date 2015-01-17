<?php

class GaussDev_Notifications_Model_Resource_Device_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('notifications/device');
    }
}