<?php
class Beagles_Login_Model_Api extends Mage_Api_Model_Resource_Abstract
{

    public function login($email, $firstName, $lastName)
    {
    	$websiteId = 1;
        $store = Mage::app()->getStore();

    	$customer = Mage::getModel("customer/customer");
    	$customerObj = $customer->setWebsiteId($websiteId)->loadByEmail($email);

        $session = Mage::getSingleton('customer/session');
    	$response = array();

    	if ($customerObj->getId()) {
            $session->setCustomerAsLoggedIn($customer);
    	} else {
    		$password = Mage::helper('core')->getRandomString($length=7);
	        $customer->setWebsiteId($websiteId)
                 ->setStore($store)
                 ->setFirstname($firstName)
                 ->setLastname($lastName)
                 ->setEmail($email)
                 ->setPassword($password)
                 ->save();
    	}
        $response = array('firstName'=>$customerObj->getFirstname(), 'lastName'=>$customerObj->getLastname(), 'customerId'=>$customerObj->getId());
		return $response;
    }
}