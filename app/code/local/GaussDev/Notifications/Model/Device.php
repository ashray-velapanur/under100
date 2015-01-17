<?php

/**
 * @method GaussDev_Notifications_Model_Device setEntityId(int $value)
 * @method int getCustomerId()
 * @method GaussDev_Notifications_Model_Device setCustomerId(int $value)
 * @method string getDeviceId()
 * @method GaussDev_Notifications_Model_Device setDeviceId(string $value)
 * @method string getType()
 * @method GaussDev_Notifications_Model_Device setType(string $value)
 */
class GaussDev_Notifications_Model_Device extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('notifications/device');
    }
}