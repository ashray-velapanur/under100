<?php

class GaussDev_Notifications_Model_Resource_Notification extends Mage_Core_Model_Resource_Db_Abstract
{

    protected function _construct()
    {
        $this->_init('notifications/notification', 'entity_id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ((!$object->getId() || $object->isObjectNew()) && !$object->getCreatedAt()) {
            $object->setCreatedAt(Varien_Date::now());
        }

        if (!$object->getRenderedText()) {
            $object->setRenderedText($object->getTypeModel()->getText());
        }
        if (!$object->getRenderedImageUrl()) {
            $object->setRenderedImageUrl($object->getTypeModel()->getImage());
        }

        if ($object->getRerenderData()) {
            $object->setRenderedText($object->getTypeModel()->getText())->setRenderedImageUrl($object->getTypeModel()->getImage());
        }

        return parent::_beforeSave($object);
    }
}