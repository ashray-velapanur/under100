<?php

class GaussDev_SocialLogin_Model_Resource_Oauth_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('gaussdev_sociallogin/oauth');
    }

}