<?php
require_once(Mage::getBaseDir() . DS . 'vendor' . DS . 'autoload.php');

class GaussDev_SocialLogin_OauthController extends Mage_Core_Controller_Front_Action
{
    public function authAction()
    {
        $session = Mage::getSingleton('core/session');
        if ($this->getRequest()->getParam('mobile')) {
            $session->setData('is_mobile_client', true);
        }
        new Opauth(Mage::helper('gaussdev_sociallogin')->opauthConfig());
    }

    public function callbackAction()
    {
        Mage::getSingleton('core/session')->setData('is_first_load', true);
        try {
            $opauth = new Opauth(Mage::helper('gaussdev_sociallogin')->opauthConfig(), false);
            $response = $_SESSION['opauth'];
            unset($_SESSION['opauth']);

            if (array_key_exists('error', $response)) {
                throw new Exception(print_r($response['error'], true));
            } else {
                if (empty($response['auth']) || empty($response['timestamp']) || empty($response['signature'])
                    || empty($response['auth']['provider'])
                    || empty($response['auth']['uid'])
                ) {
                    throw new Exception('Missing key auth response components.');
                } elseif (!$opauth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'],
                    $response['signature'], $reason)
                ) {
                    throw new Exception('Invalid auth response: ' . $reason);
                } else {
                    $customer = Mage::getModel('gaussdev_sociallogin/oauth')->processCustomer($response);
                    $this->_redirect('*/*/response', array(
                        '_query' => array(
                            'success'     => 'true',
                            'customer_id' => $customer->getId(),
                        )
                    ));
                    $this->getResponse()->sendHeadersAndExit();
                }
            }
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
        }
        $this->_redirect('*/*/response', array('_query' => array('success' => 'false')));
    }

    public function responseAction()
    {
        $session = Mage::getSingleton('core/session');

        $isMobileOrNotFirst = $session->getData('is_mobile_client') === true
            || $session->getData('is_first_load') !== true;
        $session->unsetData('is_mobile_client');
        $session->unsetData('is_first_load');
        if ($isMobileOrNotFirst) {
            $this->getResponse()->clearAllHeaders()->clearBody();
            exit;
        }
        Mage::getSingleton('customer/session')->setCustomerAsLoggedIn(Mage::getModel('customer/customer')
                                                                          ->load($this->getRequest()
                                                                                      ->getParam('customer_id')));
        $this->_loginPostRedirect();
    }

    protected function _loginPostRedirect()
    {
        $session = Mage::getSingleton('customer/session');

        if (!$session->getBeforeAuthUrl()) {
            $session->setBeforeAuthUrl(Mage::helper('customer')->getAccountUrl());
            if ($session->isLoggedIn()) {
                if (!Mage::getStoreConfigFlag(Mage_Customer_Helper_Data::XML_PATH_CUSTOMER_STARTUP_REDIRECT_TO_DASHBOARD)) {
                    $referer = $this->getRequest()->getParam(Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME);
                    if ($referer) {
                        $referer = Mage::getModel('core/url')->getRebuiltUrl(Mage::helper('core')->urlDecode($referer));
                        if ($this->_isUrlInternal($referer)) {
                            $session->setBeforeAuthUrl($referer);
                        }
                    }
                } else {
                    if ($session->getAfterAuthUrl()) {
                        $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                    }
                }
            } else {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getLoginUrl());
            }
        } else {
            if ($session->getBeforeAuthUrl() == Mage::helper('customer')->getLogoutUrl()) {
                $session->setBeforeAuthUrl(Mage::helper('customer')->getDashboardUrl());
            } else {
                if (!$session->getAfterAuthUrl()) {
                    $session->setAfterAuthUrl($session->getBeforeAuthUrl());
                }
                if ($session->isLoggedIn()) {
                    $session->setBeforeAuthUrl($session->getAfterAuthUrl(true));
                }
            }
        }
        $this->_redirectUrl($session->getBeforeAuthUrl(true));
    }
}