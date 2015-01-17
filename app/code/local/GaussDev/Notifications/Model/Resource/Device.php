<?php

class GaussDev_Notifications_Model_Resource_Device extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('notifications/device', 'entity_id');
    }
}