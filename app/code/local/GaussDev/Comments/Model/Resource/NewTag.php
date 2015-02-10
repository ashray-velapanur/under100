<?php

class GaussDev_Comments_Model_Resource_Newtag extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('gaussdev_comments/newtag', 'id');
    }

}