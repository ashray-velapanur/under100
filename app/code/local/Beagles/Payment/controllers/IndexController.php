<?php

require_once Mage::getBaseDir('lib').DS.'Stripe'.DS.'Stripe.php';

class Beagles_Payment_IndexController extends Mage_Core_Controller_Front_Action {        

	public function indexAction() {
		echo '...working!';
	}

    public function chargeAction() {
 	   	Stripe::setApiKey("sk_test_Vg3W1zSrXyEeW91b072mpnm9");

		$token = $this->getRequest()->getPost('token');
		$product_id = intval($this->getRequest()->getPost('product_id'));
		$user_id = intval($this->getRequest()->getPost('user_id'));

		if (!$token || !$product_id || !$user_id) {
			return;
		}
		$product = Mage::getModel('catalog/product')->load($product_id);
		$priceInCents = $product->getPrice() * 100;

		try {
			$charge = Stripe_Charge::create(array(
			  "amount" => $priceInCents,
			  "currency" => "usd",
			  "card" => $token,
			  "description" => "payinguser@example.com")
			);
		} catch(Stripe_CardError $e) {
			Mage::log('cc error');
		}
	}
}