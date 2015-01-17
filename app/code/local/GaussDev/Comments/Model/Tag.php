<?php

class GaussDev_Comments_Model_Tag extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('gaussdev_comments/tag');
    }

    public function afterLoad()
    {
        parent::_afterLoad();
        $customer = Mage::getModel('customer/customer')->load($this->getCustomerId());
        $this->setData('customer', $customer->getId() ? $customer : null);
    }

    public function toArray(array $arrAttributes = array())
    {
        $arrAttributes = array('customer');
        foreach ($arrAttributes as $attribute) {
            if (isset($this->_data[$attribute]) && is_object($this->_data[$attribute])
                && method_exists($this->_data[$attribute], 'toArray')
            ) {
                $this->_data[$attribute] = $this->_data[$attribute]->toArray();
            }
        }

        return $this->getData();
    }
}