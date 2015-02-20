<?php
class Beagles_Login_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	public function facebookLogin($userId, $accessToken){
		$client = new Varien_Http_Client('https://graph.facebook.com/me');
		$client->setMethod(Varien_Http_Client::GET);
		$client->setParameterGet('access_token', $accessToken);
		$client->setParameterGet('fields', 'first_name, last_name, id, email');

		$response = array();
		$response['success'] = FALSE;

	    $facebookResponse = $client->request();

	    if ($facebookResponse->isSuccessful()) {
	    	$profile = json_decode($facebookResponse->getBody(), TRUE);

	    	if ($profile['id'] == strval($userId)) {
	    		Mage::log($profile['id']);
		    	$websiteId = 0;
	        	$store = Mage::app()->getStore();
    			$customer = Mage::getModel('customer/customer');
		        $session = Mage::getSingleton('customer/session');

    			$customerObj = $customer->setWebsiteId($websiteId)->loadByEmail($profile['email']);

		    	if ($customerObj->getId()) {
		    		Mage::log('email exists');
		            $session->setCustomerAsLoggedIn($customerObj);
		            $response['firstName'] = $customer->getFirstname();
		            $response['lastName'] = $customer->getLastname();
		            $response['customerid'] = $customer->getId();
		    	} else {
		    		Mage::log('email no exists');
		    		$password = Mage::helper('core')->getRandomString($length=7);
			        $customer->setWebsiteId($websiteId)
		                 ->setStore($store)
		                 ->setFirstname($profile['first_name'])
		                 ->setLastname($profile['last_name'])
		                 ->setEmail($profile['email'])
		                 ->setPassword($password)
		                 ->save();
		            $session->setCustomerAsLoggedIn($customer);
		            $response['firstName'] = $profile['first_name'];
		            $response['lastName'] = $profile['last_name'];
		            $response['customerid'] = $customer->getId();
		    	}
				$response['success'] = TRUE;
	    	}
	    }
	    return $response;
	}


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