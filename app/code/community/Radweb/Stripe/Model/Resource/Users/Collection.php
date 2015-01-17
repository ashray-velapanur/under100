<?php
/**
 * News collection
 *
 * @author Magento
 */
class Radweb_Stripe_Model_Resource_Users_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Define collection model
     */
    protected function _construct()
    {
        $this->_init('radweb_stripe/users');
    }

}