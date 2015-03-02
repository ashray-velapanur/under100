<?php
class Beagles_Brands_Model_Api extends Mage_Api_Model_Resource_Abstract
{
	public function brands(){
		Mage::log('... brands');
		$baseUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
		$brands = Mage::getModel('brands/brands')
					->getCollection();
					#->addFieldToFilter('name', strtolower($brandName));
		$response = array();
		foreach ($brands as $brand) {
			$response[] = array('name'=>$brand->getName(), 'image_url'=>$baseUrl.$brand->getImageUrl(), 'description'=>$brand->getDescription());
		}
		return $response;
	}
}