<?php
class Beagles_Rewards_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	public function level($customerId){
		$clicks = Mage::getModel('rewards/clicks')->getCollection()->addFieldToFilter('uid', $customerId);
		#$comments = Mage::getModel('gaussdev_comments/comment')->getCollection()->addFieldToFilter('customer_id', $customerId);
		$products = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('product_owner_id', $customerId);
		$productLikes = 0;
		$productComments = 0;
		foreach ($products as $product) {
			$productId = $product->getId();
	        $productLikes += Mage::helper('GaussDev_Like')->countLikes($productId);
	        $productComments += Mage::helper('gaussdev_comments')->commentCount($productId);
		}
		return array('clicks'=>count($clicks), 'likes'=>$productLikes, 'comments'=>$productComments, 'products'=>count($products));
	}
}