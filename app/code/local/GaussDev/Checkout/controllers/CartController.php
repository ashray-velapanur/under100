<?php
require_once 'Mage/Checkout/controllers/CartController.php';

class GaussDev_Checkout_CartController extends Mage_Checkout_CartController
{

    public function addAction()
    {
        $product = $this->_initProduct();
        if ($product && $product->getIsVerified() == false && $product->getProductOriginUrl() == true) {
            $url = $product->getProductOriginUrl();

            $this->_redirectUrl('http://redirect.viglink.com?' . http_build_query(array(
                    'u'   => $url,
                    'key' => '1507a657fb951e5ad5dfb7be2cc13adf'
                )));
            $this->getResponse()->sendHeadersAndExit();
        } else {
            parent::addAction();
        }
    }

    public function deleteItemAction()
    {
        try {
            if (!$this->_validateFormKey()) {
                throw new Exception('Invalid form key.');
            }

            $id = (int)$this->getRequest()->getParam('id');
            if ($id) {
                try {
                    $this->_getCart()->removeItem($id)->save();
                } catch (Exception $e) {
                    $this->_getSession()->addError($this->__('Cannot remove the item.'));
                    Mage::logException($e);
                }
            }
        } catch (Exception $e) {
        }
        $this->_redirectReferer('onestepcheckout', array('_secure' => true));
    }

}