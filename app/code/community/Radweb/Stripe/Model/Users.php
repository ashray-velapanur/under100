<?php
/**
 * News item model
 *
 * @author Magento
 */
class Radweb_Stripe_Model_Users extends Mage_Core_Model_Abstract
{
    /**
     * Define resource model
     */
    protected function _construct()
    {
        $this->_init('radweb_stripe/users');
    }

    public function loadById($id)
    {
        $this->load($id, 'user_id');
        return $this;
    }

}