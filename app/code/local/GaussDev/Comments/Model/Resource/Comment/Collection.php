<?php

class GaussDev_Comments_Model_Resource_Comment_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected $_getCustomerLiked = false;
    protected $_loadRepliesCollection = false;

    protected function _construct()
    {
        $this->_init('gaussdev_comments/comment');
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $fn = Mage::getModel('eav/entity_attribute')->loadByCode('1', 'firstname');
        $ln = Mage::getModel('eav/entity_attribute')->loadByCode('1', 'lastname');
        $im = Mage::getModel('eav/entity_attribute')->loadByCode('1', 'profile_image');

        $this->getSelect()
             ->joinLeft(array('ce1' => 'customer_entity_varchar'),
                 'ce1.entity_id=main_table.customer_id and ce1.attribute_id=' . $fn->getAttributeId(),
                 array('firstname' => 'value'))
             ->joinLeft(array('ce2' => 'customer_entity_varchar'),
                 'ce2.entity_id=main_table.customer_id and ce2.attribute_id=' . $ln->getAttributeId(),
                 array('lastname' => 'value'))
             ->joinLeft(array('ce3' => 'customer_entity_varchar'),
                 'ce3.entity_id=main_table.customer_id and ce3.attribute_id=' . $im->getAttributeId(),
                 array('profile_image' => 'value'))
             ->distinct();

        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->walk('afterLoad', array(
            array(
                'loadRepliesCollection' => $this->_loadRepliesCollection,
                'getCustomerLiked'      => $this->_getCustomerLiked
            )
        ));

        return $this;
    }

    public function setLoadRepliesCollection($bool)
    {
        $this->_loadRepliesCollection = (bool)$bool;

        return $this;
    }

    public function getCustomerLiked($customerId)
    {
        $this->_getCustomerLiked = $customerId;

        return $this;
    }

    public function toArray($arrRequiredFields = array())
    {
        $arrItems = array();
        foreach ($this as $item) {
            $arrItems[] = $item->toArray($arrRequiredFields);
        }

        return $arrItems;
    }
}