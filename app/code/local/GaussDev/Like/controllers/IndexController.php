<?php
/**
 * Like - custom magnento module for Under100
 *
 * @author seb
 * @copyright Sebastijan Placento - 9a3bsp@gmail.com , GaussDevelopment gauss-development.com
 */


class GaussDev_Like_IndexController extends Mage_Core_Controller_Front_Action{
	private $helper;

	public function _construct(){
		$this->helper=Mage::helper('GaussDev_Like');

	}

    public function AddAction() { //      /like/index/add/product/77
		$session = Mage::getSingleton('customer/session');
		$productID= $this->getRequest()->getParam('product');
		if(!$session->isLoggedIn() || empty($productID)) exit("Not logged in."); //user isnt logged in.

		$uid= $session->getId();
		$result= $this->helper->addLike($productID, $uid);
		if(!$result) exit("Error saving into DB");

		echo $this->helper->countLikes($productID);

    }

    public function checkLikedAction(){
    	$session = Mage::getSingleton('customer/session');
    	$productID= $this->getRequest()->getParam('product');
    	if(!$session->isLoggedIn() || empty($productID)) exit("Not logged in."); //user isnt logged in.

    	$result= $this->helper->checkLiked($productID);
    	if($result>0) echo 1;else echo 0;

    }



    public function testAction()  {
    	echo "<pre>";
    	var_dump($this->helper->getLiked());
    	return $this->helper->getLiked();


    }

}














// 	  $this->loadLayout();
// 	  $this->getLayout()->getBlock("head")->setTitle($this->__("Like"));
// 	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
//       $breadcrumbs->addCrumb("home", array(
//                 "label" => $this->__("Home Page"),
//                 "title" => $this->__("Home Page"),
//                 "link"  => Mage::getBaseUrl()
// 		   ));

//       $breadcrumbs->addCrumb("like", array(
//                 "label" => $this->__("Like"),
//                 "title" => $this->__("Like")
// 		   ));

//       $this->renderLayout();