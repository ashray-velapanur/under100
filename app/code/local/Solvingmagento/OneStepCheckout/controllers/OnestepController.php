<?php
/**
 * Solvingmagento_OneStepCheckout controller class
 *
 * PHP version 5.3
 *
 * @category  Solvingmagento
 * @package   Solvingmagento_OneStepCheckout
 * @author    Oleg Ishenko <oleg.ishenko@solvingmagento.com>
 * @copyright 2014 Oleg Ishenko
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @version   GIT: <0.1.0>
 * @link      http://www.solvingmagento.com/
 *
 */

/** Solvingmagento_OneStepCheckout_OnestepController
 *
 * @category Solvingmagento
 * @package  Solvingmagento_OneStepCheckout
 *
 * @author   Oleg Ishenko <oleg.ishenko@solvingmagento.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @version  Release: <package_version>
 * @link     http://www.solvingmagento.com/
 */
require_once 'Mage/Checkout/controllers/OnepageController.php';

class Solvingmagento_OneStepCheckout_OnestepController extends Mage_Checkout_OnepageController
{
    /**
     * Check if a guest can proceed to the checkout
     *
     * @return boolean
     */
    protected function _canShowForUnregisteredUsers()
    {
        $guestAllowed = Mage::getSingleton('customer/session')->isLoggedIn()
            || Mage::helper('checkout')->isAllowedGuestCheckout($this->getOnestep()->getQuote())
            || !Mage::helper('checkout')->isCustomerMustBeLogged();

        if (!$guestAllowed) {
            Mage::getSingleton('customer/session')->addError(Mage::helper('checkout')
                                                                 ->__('Please login or register to continue to the checkout'));
            $this->_redirect('customer/account/edit');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);

            //return true to the caller method _preDispatch so that it doesn't redirect to the 404 page
            return true;
        } else {
            return true;
        }

    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        $ignoreItemError = Mage::getSingleton('checkout/session')->getData('firstload', true);
        Mage::getSingleton('checkout/session')->unsetData('firstload');
        if (!$this->getOnestep()->getQuote()->hasItems()
            || ($this->getOnestep()->getQuote()->getHasError() && !$ignoreItemError)
            || $this->getOnestep()->getQuote()->getIsMultiShipping()
        ) {
            $this->_ajaxRedirectResponse();

            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)
            && !in_array($action, array('index', 'progress'))
        ) {
            $this->_ajaxRedirectResponse();

            return true;
        }

        return false;
    }

    /**
     * Get one page checkout model
     *
     * @return Solvingmagento_OneStepCheckout_Model_Type_Onestep
     */
    public function getOnestep()
    {
        return Mage::getSingleton('slvmto_onestepc/type_onestep');
    }

    /**
     * Saves address data (billing or shipping)
     *
     * @param array  $data      an array containing address form data
     * @param int    $addressId an ID of an existing address (for logged in customers only)
     * @param string $type      billing or shipping
     * @param bool   $response  allows writing an error result directly to to the controller response
     *
     * @return array|bool
     */
    protected function saveAddressData($data, $addressId, $type, $response = true)
    {
        $type = strtolower($type);

        if ($type != 'shipping' && $type != 'billing') {
            $result = array('error' => 1, 'message' => Mage::helper('checkout')->__('Error saving checkout data'));
            if ($response) {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                return false;
            } else {
                return $result;
            }
        }
        $method = 'save' . ucwords($type);
        $result = $this->getOnestep()->$method($data, $addressId);

        if (isset($result['error'])) {
            if ($response) {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                return false;
            }
        }

        return $result;
    }

    /**
     * Get shipping method step html
     *
     * @return string
     */
    protected function _getShippingMethodsHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->addHandle('checkout_onestep_shippingmethod')->merge('checkout_onestep_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();

        return $output;
    }

    /**
     * Get payment method step html
     *
     * @return string
     */
    protected function _getPaymentMethodsHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->addHandle('checkout_onestep_paymentmethod')->merge('checkout_onestep_paymentmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();

        return $output;
    }

    /**
     * Get order review step html
     *
     * @return string
     */
    protected function _getReviewHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->addHandle('checkout_onestep_review')->merge('checkout_onestep_review');
        $layout->generateXml()->generateBlocks();
        $output = $layout->getBlock('checkout.review')->toHtml();

        return $output;
    }

    /**
     * Save the selected shipping method to the quote
     *
     * @param string $shippingMethod code of the selected shipping method
     * @param bool   $response       allows writing of an error result directly to the response
     *
     * @return array|bool
     */
    protected function saveShippingMethodData($shippingMethod, $response = true)
    {
        $result = $this->getOnestep()->saveShippingMethod($shippingMethod);

        if (isset($result['error'])) {
            if ($response) {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                return false;
            }
        }
        $this->getOnestep()->getQuote()->getShippingAddress()->setCollectShippingRates(true);

        return $result;
    }

    /**
     * Saves payment data to the quote
     *
     * @param array $data     payment data
     * @param bool  $response allows writing of an error result directly to the response
     *
     * @return array|bool
     */
    protected function savePayment($data, $response = true)
    {
        $result = array();

        try {
            $result = $this->getOnestep()->savePayment($data);

            // get section and redirect data
            $redirectUrl = $this->getOnestep()->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = 1;
            $resutl['message'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = 1;
            $result['message'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = 1;
            $result['message'] = $this->__('Unable to set Payment Method.');
        }

        if (isset($result['error'])) {
            if ($response) {
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                return false;
            }
        }

        return $result;
    }

    /**
     * Checkout page
     */
    public function indexAction()
    {
        if (!Mage::helper('slvmto_onestepc')->oneStepCheckoutEnabled()) {
            Mage::getSingleton('checkout/session')->addError($this->__('One Step checkout is disabled.'));
            $this->_redirect('');

            return;
        }
        $quote = $this->getOnestep()->getQuote();
        if (!$quote->hasItems()) {
            $this->_redirect('');

            return;
        }

        Mage::getSingleton('checkout/session')->setData('firstload', true);

        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure' => true)));
        $this->getOnestep()->initCheckout();
        $this->loadLayout();
        $this->initLayoutMessages(array('checkout/session', 'customer/session', 'core/session'));
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
    }

    /**
     * Saves the checkout method: guest or register
     */
    public function saveMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $method = $this->getRequest()->getPost('checkout_method');
            $result = $this->getOnestep()->saveCheckoutMethod($method);
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Updates the available selection of shipping method by saving the address data first
     */
    public function updateShippingMethodsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $post = $this->getRequest()->getPost();
        $result = array('error' => 1, 'message' => Mage::helper('checkout')->__('Error saving checkout data'));

        if ($post) {

            $result = array();

            $shipping = $this->getRequest()->getPost('shipping', array());
            $shippingAddressId = $this->getRequest()->getPost('shipping_address_id');
            $defaultBilling = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBillingAddress();
            $billingAddressId = $defaultBilling ? $defaultBilling->getId() : null;
            if (!$billingAddressId) {
                $shipping['same_as_billing'] = '1';
            } else {
                $shipping['same_as_billing'] = '0';
            }
            $this->saveAddressData($shipping, $billingAddressId, 'billing', false);
            $this->saveAddressData($shipping, $shippingAddressId, 'shipping', false);

            /* check quote for virtual */
            if ($this->getOnestep()->getQuote()->isVirtual()) {
                $result['update_step']['shipping_method'] = $this->_getShippingMethodsHtml('none');
            } else {
                $result['update_step']['shipping_method'] = $this->_getShippingMethodsHtml();
            }
        }

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Shipping method save action
     */
    public function saveShippingMethodAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $result = $this->getOnestep()->saveShippingMethod($data);
            /*
            $result will have error data if shipping method is empty
            */
            if (!isset($result['error'])) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array(
                    'request' => $this->getRequest(),
                    'quote'   => $this->getOnestep()->getQuote()
                ));
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                $result['update_step']['payment_method'] = $this->_getPaymentMethodsHtml();
            }
            //update totals
            $this->getOnestep()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->getOnestep()->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();

            $result['update_step']['review'] = $this->_getReviewHtml();
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    /**
     * Updates the available selection of payment methods by saving address and shipping  method data first
     */
    public function updatePaymentMethodsAction()
    {
        if ($this->_expireAjax()) {
            return;
        }
        $post = $this->getRequest()->getPost();
        $result = array('error' => 1, 'message' => Mage::helper('checkout')->__('Error saving checkout data'));

        if ($post) {

            $billing = isset($post['billing']) ? $post['billing'] : null;
            $shipping = isset($post['shipping']) ? $post['shipping'] : null;
            $usingCase = isset($billing['use_for_shipping']) ? (int)$billing['use_for_shipping'] : 0;
            $billingAddressId = isset($post['billing_address_id']) ? (int)$post['billing_address_id'] : false;
            $shippingAddressId = isset($post['shipping_address_id']) ? (int) $post['shipping_address_id'] : false;
            $shippingMethod = $this->getRequest()->getPost('shipping_method', '');

            if ($this->saveAddressData($billing, $billingAddressId, 'billing') === false) {
                return;
            }

            if ($usingCase <= 0) {
                if ($this->saveAddressData($shipping, $shippingAddressId, 'shipping') === false) {
                    return;
                }
            }

            $result = $this->getOnestep()->saveShippingMethod($shippingMethod);

            if (!isset($result['error'])) {
                $result['update_step']['payment_method'] = $this->_getPaymentMethodsHtml();
            }
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Updates the order review step by attempting to save the current checkout state
     */
    public function updateOrderReviewAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        $post = $this->getRequest()->getPost();
        $result = array('error' => 1, 'message' => Mage::helper('checkout')->__('Error saving checkout data'));

        if ($post) {
            $quote = $this->getOnestep()->getQuote();
            $this->_updateShoppingCart();
            $message = isset($post['gift_message'], $post['gift_check']) ? $post['gift_message'] : null;
            if (!$quote->getGiftMessageId() && $message) {
                $giftMessage = Mage::getModel('giftmessage/message');
                $giftMessage->setMessage($message)->setCustomerId(Mage::getSingleton('customer/session')
                                                                      ->getCustomerId())->save();
                $quote->setGiftMessageId($giftMessage->getId())->save();
            } elseif ($quote->getGiftMessageId() && !$message) {
                $giftMessage = Mage::getModel('giftmessage/message')->load($quote->getGiftMessageId());
                $giftMessage->delete();
                $quote->setGiftMessageId(0)->save();
            }
            $result = array();
            $shipping = $this->getRequest()->getPost('shipping', array());
            $shippingAddressId = $this->getRequest()->getPost('shipping_address_id');
            $defaultBilling = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBillingAddress();
            $billingAddressId = $defaultBilling ? $defaultBilling->getId() : null;
            if (!$billingAddressId) {
                $shipping['same_as_billing'] = '1';
            } else {
                $shipping['same_as_billing'] = '0';
            }
            $this->saveAddressData($shipping, $billingAddressId, 'billing', false);
            $this->saveAddressData($shipping, $shippingAddressId, 'shipping', false);

            $shippingMethod = $this->getRequest()->getPost('shipping_method');
            $paymentMethod = $this->getRequest()->getPost('payment', array());

            if (!$quote->isVirtual()) {
                $this->saveShippingMethodData($shippingMethod, false);
            }

            $this->savePayment($paymentMethod, false);

            //update totals
            $quote->setTotalsCollectedFlag(false)->collectTotals()->save();

            $result['update_step']['review'] = $this->_getReviewHtml();
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Submits all step data, checks for errors, saves order
     */
    public function submitOrderAction()
    {
        if ($this->_expireAjax()) {
            return;
        }

        $redirectUrl = null;
        $post = $this->getRequest()->getPost();
        $result = array(
            'error'   => 1,
            'message' => Mage::helper('checkout')->__('Error saving checkout data')
        );

        if ($post) {
            try {
                if ($requiredAgreements = Mage::helper('checkout')->getRequiredAgreementIds()) {
                    $postedAgreements = array_keys($this->getRequest()->getPost('agreement', array()));
                    if ($diff = array_diff($requiredAgreements, $postedAgreements)) {
                        $result['success'] = false;
                        $result['error'] = true;
                        $result['message'] = $this->__('Please agree to all the terms and conditions before placing the order.');
                        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));

                        return;
                    }
                }

                $quote = $this->getOnestep()->getQuote();
                $this->_updateShoppingCart();
                $message = isset($post['gift_message'], $post['gift_check']) ? $post['gift_message'] : null;
                if (!$quote->getGiftMessageId() && $message) {
                    $giftMessage = Mage::getModel('giftmessage/message');
                    $giftMessage->setMessage($message)->setCustomerId(Mage::getSingleton('customer/session')
                                                                          ->getCustomerId())->save();
                    $quote->setGiftMessageId($giftMessage->getId())->save();
                } elseif ($quote->getGiftMessageId() && !$message) {
                    $giftMessage = Mage::getModel('giftmessage/message')->load($quote->getGiftMessageId());
                    $giftMessage->delete();
                    $quote->setGiftMessageId(0)->save();
                }

                $result = array();
                $shipping = $this->getRequest()->getPost('shipping', array());
                $shippingAddressId = $this->getRequest()->getPost('shipping_address_id');
                $defaultBilling = Mage::getSingleton('customer/session')->getCustomer()->getDefaultBillingAddress();
                $billingAddressId = $defaultBilling ? $defaultBilling->getId() : null;
                if (!$billingAddressId) {
                    $shipping['same_as_billing'] = '1';
                } else {
                    $shipping['same_as_billing'] = '0';
                }
                $this->saveAddressData($shipping, $billingAddressId, 'billing', false);
                $this->saveAddressData($shipping, $shippingAddressId, 'shipping', false);

                $shippingMethod = $this->getRequest()->getPost('shipping_method');
                $paymentMethod = $this->getRequest()->getPost('payment', array());

                if (!$this->getOnestep()->getQuote()->isVirtual()) {
                    $results[] = $this->saveShippingMethodData($shippingMethod, false);
                }

                $results[] = $this->savePayment($paymentMethod, false);

                if ($data = $this->getRequest()->getPost('payment', false)) {
                    $this->getOnestep()->getQuote()->getPayment()->importData($data);
                }

                foreach ($results as $stepResult) {
                    if (isset($stepResult['error'])) {
                        $result['error'] = 1;
                        if (!isset($result['message'])) {
                            $result['message'] = array();
                        }
                        if (isset($stepResult['message'])) {
                            if (is_array($stepResult['message'])) {
                                $result['message'] = array_merge($result['message'], $stepResult['message']);
                            } else {
                                $result['message'][] = $stepResult['message'];
                            }
                        }
                    }
                }
                if (isset($result['error'])) {
                    if ($result['message']) {
                        throw new Mage_Core_Exception(implode("\n", $result['message']));
                    }
                }
                $this->getOnestep()->saveOrder();
                $result['success'] = 1;

                $redirectUrl = $this->getOnestep()->getCheckout()->getRedirectUrl();

            } catch (Mage_Payment_Model_Info_Exception $e) {

                $result['success'] = false;
                $result['error'] = 1;
                $message = $e->getMessage();
                if (!empty($message)) {
                    $result['message'] = $message;
                }

            } catch (Mage_Core_Exception $e) {
                Mage::logException($e);
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnestep()->getQuote(), $e->getMessage());

                $result['success'] = false;
                $result['error'] = true;
                $result['message'] = $e->getMessage();

            } catch (Exception $e) {
                Mage::logException($e);
                Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnestep()->getQuote(), $e->getMessage());
                $result['success'] = false;
                $result['error'] = true;
                $result['message'] = $this->__('There was an error processing your order. Please contact us or try again later.');
            }

            if ((!isset($result['success'])) || (isset($result['success']) && $result['success'] == false)) {
                /**
                 * Update the steps if there is an error
                 */
                $result['update_step']['shipping_method'] = $this->_getShippingMethodsHtml();
                $result['update_step']['payment_method'] = $this->_getPaymentMethodsHtml();
            }

            $this->getOnestep()->getQuote()->save();

            /**
             * when there is redirect to third party, we don't want to save order yet.
             * we will save the order in return action.
             */
            if (!empty($redirectUrl)) {
                $result['redirect'] = $redirectUrl;
            }
        }

        $result['update_step']['review'] = $this->_getReviewHtml();
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _updateShoppingCart()
    {
        try {
            $quote = $this->getOnestep()->getQuote();
            $cartData = $this->getRequest()->getPost('cart');
            if (is_array($cartData)) {
                $filter = new Zend_Filter_LocalizedToNormalized(array(
                    'locale' => Mage::app()->getLocale()->getLocaleCode()
                ));
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                        $item = $quote->getItemById($index);
                        if ($item->getQty() != $cartData[$index]['qty']) {
                            $item->setQty($cartData[$index]['qty']);
                        }
                    }
                }
            }
            $quote->getShippingAddress()->setCollectShippingRates(true)->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function deleteItemAction()
    {
        try {
            if (!$this->_validateFormKey()) {
                throw new Exception;
            }

            $id = $this->getRequest()->getParam('id');
            $this->getOnestep()->getQuote()->removeItem($id)->setTotalsCollectedFlag(false)->collectTotals()->save();
            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
        } catch (Exception $e) {
        }
        $this->_redirect('checkout/onestep/');
    }
}
