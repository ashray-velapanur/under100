<?php

class GaussDev_SocialLogin_Model_Resource_Customer_Customer extends Mage_Customer_Model_Resource_Customer
{
    protected function _beforeSave(Varien_Object $customer)
    {
        if (!$customer->getEmail()) {
            Mage_Eav_Model_Entity_Abstract::_beforeSave($customer);
        } else {
            parent::_beforeSave($customer);
        }

        return $this;
    }
}