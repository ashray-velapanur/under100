<?php
class Beagles_Emails_IndexController extends Mage_Core_Controller_Front_Action {        

    public function indexAction() {

        echo 'Emails Index!';

    }

    public function welcomeAction(){
    	if (!$this->getRequest()->isPost()) {
    		return;
    	}

    	$name = $this->getRequest()->getPost('name');
    	$to = $this->getRequest()->getPost('email');

		$templateId = 'welcome_email';
		$emailTemplate  = Mage::getModel('core/email_template')->loadDefault($templateId);

		$templateVariables = array(
		    'name' => $name
		);

		$senderName = 'Under 100';
		$senderEmail = 'support@theunder100.com';
		$emailTemplate->setSenderName($senderName);
		$emailTemplate->setSenderEmail($senderEmail); 

		$emailTemplate->send($to, $name, $templateVariables);  

    }
}