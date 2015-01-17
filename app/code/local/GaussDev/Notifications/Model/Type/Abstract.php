<?php

/**
 * @method GaussDev_Notifications_Model_Notification getNotification()
 **/
abstract class GaussDev_Notifications_Model_Type_Abstract extends Varien_Object
{
    protected $_initiator;

    abstract public function getText();

    abstract public function getImage();

    /**
     * @return Mage_Customer_Model_Customer|false
     */
    public function getInitiator()
    {
        if (!$this->_initiator) {
            $this->_initiator = Mage::getModel('customer/customer')->load($this->getInitiatorId());
        }

        return $this->_initiator;
    }

    abstract public function getInitiatorId();

    protected function _getPlaceholder()
    {
        return Mage::getDesign()->getSkinUrl(
            'images/logos/Under100_alt_small.png',
            array(
                '_theme'   => 'base',
                '_package' => 'under',
                'area'     => '_frontend',
                '_type'    => 'skin',
                '_default' => false
            )
        );
    }
}