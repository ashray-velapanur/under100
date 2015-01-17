<?php
/**
 * Stripe payment helper
 *
 * @category	Radweb
 * @package		Radweb_Stripe
 * @author		Artur Salagean <info@radweb.co.uk>
 * @copyright	Radweb (http://radweb.co.uk)
 * @license		http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Radweb_Stripe_Helper_Data extends Mage_Core_Helper_Abstract
{

	const XML_PATH_SAVECC = "payment/radweb_stripe/savecc";


	public function getEnabledCardTypes()
	{
		$storeId = Mage::app()->getStore()->getId();
		$cctypes = Mage::getStoreConfig('payment/radweb_stripe/cctypes', $storeId); 

		return $cctypes;
	}

	public function getAvailableCardTypes()
	{
		$cardTypes = array
		(
			"AE" => "American Express",
			"VI" => "Visa",
			"MC" => "Mastercard",
			"DI" => "Discover",
			"JCB" => "JCB",
			"DIN" => "Diners Club"
		);
		return $cardTypes;
	}

	public function canSaveCC() 
	{
		$storeId = Mage::app()->getStore()->getId();
		$saveCC = Mage::getStoreConfig('payment/radweb_stripe/savecc', $storeId);
		//var_dump($storeId);
		//var_dump($saveCC);
		return $saveCC;
	}
}