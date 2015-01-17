<?php
class GaussDev_Follow_IndexController extends Mage_Core_Controller_Front_Action{
    
	
	
	public function addAction() {
		$helper= mage::helper("GaussDev_Follow");
		$follow= $this->getRequest()->getParam('follow');
		if(empty($follow)) exit();
		if($helper->addFollower('',$follow)) echo 1; else echo 0;
		exit();	  
    }
}