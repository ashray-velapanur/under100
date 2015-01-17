<?php

class GaussDev_Comments_Model_Resource_Tag_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{

    protected function _construct()
    {
        $this->_init('gaussdev_comments/tag');
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->walk('afterLoad');

        return $this;
    }

    public function toArray($arrAttributes = array())
    {
        $arrItems = array();
        foreach ($this as $item) {
            $arrItems[] = $item->toArray();
        }

        return $arrItems;
    }
}