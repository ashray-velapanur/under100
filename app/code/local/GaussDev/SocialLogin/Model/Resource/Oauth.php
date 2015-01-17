<?php

class GaussDev_SocialLogin_Model_Resource_Oauth extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('gaussdev_sociallogin/oauth', 'entity_id');
    }

}