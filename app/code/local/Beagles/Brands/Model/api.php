<?php
class Beagles_Brands_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	public function brands($brandName){
		$brands = Mage::getModel('brands/brands')
					->getCollection()
					->addFieldToFilter('name', strtolower($brandName));
		$response = array();
		foreach ($brands as $brand) {
			$response = array('name'=>$brand->getName(), 'image_url'=>$brand->getImageUrl(), 'description'=>$brand->getDescription());
		}
		return $response;
	}
}