<?php
class GaussDev_ProductRanking_IndexController extends Mage_Core_Controller_Front_Action{
	private $helper;

	public function _construct(){
		//$this->helper=Mage::helper('productranking');
	}


    public  function getmoreAction(){
//    	$page= $this->getRequest()->getParam('page');

    	echo $this->getLayout()->createBlock('cms/block')->setBlockId('loadmore')->toHtml();
    	exit();

    }

	public function getnewarrivalsAction()
	{
		echo $this->getLayout()
				  ->createBlock('cms/widget_block')
				  ->setTemplate('cms/widget/static_block/newarrivals.phtml')
				  ->toHtml();
	}

	public function getbestsellersAction()
	{
		echo $this->getLayout()
				  ->createBlock('cms/widget_block')
				  ->setTemplate('cms/widget/static_block/bestsellers.phtml')
				  ->toHtml();
	}

    public function add2cartAction(){
    	$id=Mage::app()->getRequest()->getPost("data");
    	if(!is_numeric($id)) exit("0");
    	$_product = Mage::getModel('catalog/product')->load($id);
    	$cart = Mage::getModel('checkout/cart');
    	$cart->init();
    	$cart->addProduct($_product, array('qty' => 1));
    	$cart->save();
    	Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
    	exit("1");
    }
}
