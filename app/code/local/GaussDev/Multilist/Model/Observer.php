<?php
class GaussDev_Multilist_Model_Observer
{
	public function add2session(Varien_Event_Observer $observer)
	{
		Mage::getSingleton('core/session')->setProductAddedToCartFlag(true);
		Mage::getSingleton('core/session')->setProductAddedToCartId( $observer->getProduct()->getId());
		Mage::getSingleton('core/session')->setProductAddedToCartName( $observer->getProduct()->getName());
	}
}
