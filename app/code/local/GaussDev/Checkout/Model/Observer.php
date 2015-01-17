<?php

class GaussDev_Checkout_Model_Observer
{
    public function paymentMethodIsActive(Varien_Event_Observer $observer)
    {
        $method = $observer->getMethodInstance()->getCode();
        if ($method === 'checkmo' && Mage::getSingleton('api/server')->getAdapter() === null) {
            $observer->getResult()->isAvailable = false;
        }

        return $this;
    }
}