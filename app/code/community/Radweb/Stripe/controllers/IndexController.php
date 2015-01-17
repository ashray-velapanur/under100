<?php

/**
 * Stripe payment
 *
 * @category    Radweb
 * @package     Radweb_Stripe
 * @author      Artur Salagean <info@radweb.co.uk>
 * @copyright   Radweb (http://radweb.co.uk)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Radweb_Stripe_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');



    //redirect if not logged in
    public function preDispatch()
    {
        parent::preDispatch();

        if (!Mage::getSingleton('customer/session')->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
        
    }

    /**
     * Display customer wishlist
     */
    public function indexAction()
    {
        $this->loadLayout();

        $session = Mage::getSingleton('customer/session');
        $block   = $this->getLayout()->getBlock('stripe.cards');
        $referer = $session->getAddActionReferer(true);
        if ($block) {
            $block->setRefererUrl($this->_getRefererUrl());
            if ($referer) {
                $block->setRefererUrl($referer);
            }
        }

        $this->_initLayoutMessages('customer/session');
        $this->_initLayoutMessages('checkout/session');
        $this->_initLayoutMessages('catalog/session');
        $this->_initLayoutMessages('wishlist/session');

        $this->renderLayout();
    }



    /**
     * Remove item
     */
    public function removeAction()
    {
        require_once Mage::getBaseDir('lib').DS.'Stripe'.DS.'Stripe.php';
        $storeId = Mage::app()->getStore()->getId();
        $api_key = Mage::getStoreConfig('payment/radweb_stripe/api_key', $storeId);
        Stripe::setApiKey($api_key);

        $stripe_card = (int) $this->getRequest()->getParam('card_id');
        
        $customer = Mage::getSingleton('customer/session')->getCustomer();
                $customer_id = $customer->getId();

                //var_dump($customerId);


                $model = Mage::getModel('radweb_stripe/users');

                $stripe_user = $model->loadById($customer_id);

                //var_dump($stripe_user->getStripeToken());
                $customer_token = $stripe_user->getCustomerToken();


                if($customer_token == null)
                {

                }
                else
                {

                    $stripeCustomer = Stripe_Customer::retrieve($customer_token);
                    $cards = $stripeCustomer->cards->data;
                    $card = $cards[$stripe_card];
                    //var_dump($card);

                    $stripeCustomer->cards->retrieve($card->id)->delete();

                    //die();
              
                     Mage::getSingleton('customer/session')->addSuccess(
                $this->__('Credit card successfully deleted.')
            );

                }


        $this->_redirectReferer(Mage::getUrl('*/*'));
    }


}
