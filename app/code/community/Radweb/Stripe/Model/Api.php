<?php

include_once Mage::getBaseDir('lib') . DS . 'Stripe' . DS . 'Stripe.php';

class Radweb_Stripe_Model_Api extends Mage_Api_Model_Resource_Abstract
{

    public function __construct()
    {
        $api_key = Mage::getStoreConfig('payment/radweb_stripe/api_key');
        Stripe::setApiKey($api_key);
    }

    public function get_cards($customerId)
    {
        $cards = json_encode(array());
        $model = Mage::getModel('radweb_stripe/users');

        $stripe_user = $model->loadById($customerId);

        $customer_token = $stripe_user->getCustomerToken();

        if ($customer_token) {
            $stripeCustomer = Stripe_Customer::retrieve($customer_token);
            $cards = $stripeCustomer->cards->__toJson();
        }

        return json_decode($cards, true);
    }
}