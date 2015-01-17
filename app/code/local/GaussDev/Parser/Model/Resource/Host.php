<?php

class GaussDev_Parser_Model_Resource_Host extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('gaussdev_parser/host', 'entity_id');
    }

}