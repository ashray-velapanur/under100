<?php
/**
 * News item resource model
 *
 * @author Magento
 */
class Radweb_Stripe_Model_Resource_Users extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize connection and define main table and primary key
     */
    protected function _construct()
    {
        $this->_init('radweb_stripe/users', 'id');
    }
}