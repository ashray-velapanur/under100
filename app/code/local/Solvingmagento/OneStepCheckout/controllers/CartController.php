<?php
/**
 * Created by PhpStorm.
 * User: nino
 * Date: 9/30/14
 * Time: 11:02 PM
 */
require_once 'Mage/Checkout/controllers/CartController.php';


class Solvingmagento_OneStepCheckout_CartController extends Mage_Checkout_CartController
{
    public function indexAction()
    {
        $cart = $this->_getCart();
        if ($cart->getQuote()->getItemsCount()) {
            $cart->init();
            $cart->save();

            if (!$this->_getQuote()->validateMinimumAmount()) {
                $minimumAmount = Mage::app()
                                     ->getLocale()
                                     ->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
                                     ->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

                $warning = Mage::getStoreConfig('sales/minimum_order/description')
                    ? Mage::getStoreConfig('sales/minimum_order/description') : Mage::helper('checkout')
                                                                                    ->__('Minimum order amount is %s',
                                                                                        $minimumAmount);

                $cart->getCheckoutSession()->addNotice($warning);
            }
        }

        // Compose array of messages to add
        $messages = array();
        foreach ($cart->getQuote()->getMessages() as $message) {
            if ($message) {
                // Escape HTML entities in quote message to prevent XSS
                $message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
                $messages[] = $message;
            }
        }
        $cart->getCheckoutSession()->addUniqueMessages($messages);

        /**
         * if customer enteres shopping cart we should mark quote
         * as modified bc he can has checkout page in another window.
         */
        //$this->_getSession()->setCartWasUpdated(true);

        if ($this->_getCart()->getQuote()->getItemsCount()) {
            $this->_redirect('*/onestep/', array('_secure' => true));
        }
    }

    public function addAction()
    {
        $product = $this->_initProduct();
        if ($product && !$product->getIsVerified()) {
            $url = $product->getProductOriginUrl();
            if (!$url) {
                parent::addAction();
            } else {
                $this->_redirectUrl('http://redirect.viglink.com?' . http_build_query(array(
                        'u' => $url,
                        'key' => '1507a657fb951e5ad5dfb7be2cc13adf'
                    )));
                $this->getResponse()->sendHeadersAndExit();
            }
        } else {
            parent::addAction();
        }
    }

}