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

require_once Mage::getBaseDir('lib').DS.'Stripe'.DS.'Stripe.php';

class Radweb_Stripe_Block_Stripe extends Mage_Core_Block_Template
{

    public function __construct()
    {
        $storeId = Mage::app()->getStore()->getId();
        $api_key = Mage::getStoreConfig('payment/radweb_stripe/api_key', $storeId);
        Stripe::setApiKey($api_key);
    }
   
    /**
     * Preparing global layout
     *
     * @return Mage_Wishlist_Block_Customer_Wishlist
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $headBlock->setTitle($this->__('My Credit Cards'));
        }
    }

    /**
     * Retrieve Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    public function getItemRemoveUrl($card_id)
    {
        return $this->getUrl('manage-cards/index/remove', array(
            'card_id' => $card_id,
        ));
    
    }

  
}
