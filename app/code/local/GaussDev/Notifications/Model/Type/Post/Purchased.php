<?php

/**
 * Class GaussDev_Notifications_Model_Type_Post_Purchased
 *
 * data_id table field - catalog_product_entity.entity_id
 */
class GaussDev_Notifications_Model_Type_Post_Purchased extends GaussDev_Notifications_Model_Type_Abstract
{
    public function getText()
    {
        $text = Mage::helper('core')->__('%s was purchased.', $this->getDataModel()->getName());

        return $text;
    }

    public function getImage()
    {
        return $this->getDataModel()->getThumbnail() ?
            Mage::getModel('catalog/product_media_config')->getMediaUrl($this->getDataModel()->getThumbnail()) : $this->_getPlaceholder();
    }

    public function getInitiatorId()
    {
        return false;
    }

    /**
     * @return Mage_Catalog_Model_Product|false
     */
    protected function getDataModel()
    {
        return Mage::getModel('catalog/product')->load($this->getNotification()->getDataId());
    }
}