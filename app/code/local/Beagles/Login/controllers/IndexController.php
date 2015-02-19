<?php
class Beagles_Login_IndexController extends Mage_Core_Controller_Front_Action {        

    public function indexAction() {
    	if (!$this->getRequest()->isPost()){
    		return;
    	}
    	$email = $this->getRequest()->getPost('email');
        $firstName = $this->getRequest()->getPost('firstName');
        $lastName = $this->getRequest()->getPost('lastName');
    	$password = $this->getRequest()->getPost('password');

        $websiteId = Mage::app()->getWebsite()->getId();
        $store = Mage::app()->getStore();

        $customer = Mage::getModel("customer/customer");
        $customer->setWebsiteId($websiteId)
                 ->setStore($store)
                 ->setFirstname($firstName)
                 ->setLastname($lastName)
                 ->setEmail($email)
                 ->setPassword('beagles666');

        $session = Mage::getSingleton('customer/session');

        try {
            $customer->save();
            $session->setCustomerAsLoggedIn($customer);
            $this->getResponse()->setBody(Zend_Json::encode(array('success'=>'true', 'firstName'=>$firstName, 'lastName'=>$lastName, 'customerId'=>$customer->getId())));
        } catch(Exception $e) {
            $this->getResponse()->setBody(Zend_Json::encode(array('success'=>'false')));
        }
    }

}