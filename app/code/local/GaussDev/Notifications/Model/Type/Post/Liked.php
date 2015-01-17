<?php

/**
 * Class GaussDev_Notifications_Model_Type_Post_Liked
 *
 * data_id table field - gaussdev_like.id
 */
class GaussDev_Notifications_Model_Type_Post_Liked extends GaussDev_Notifications_Model_Type_Abstract
{
    public function getText()
    {
        $text = Mage::helper('core')->__('%s has liked your post.', Mage::helper('notifications')->getName($this->getInitiator()));

        return $text;
    }

    public function getImage()
    {
        return $this->getInitiator()->getProfileImage() ? Mage::helper('gaussdev_customerimages')->getUrl($this->getInitiator()) :
            $this->_getPlaceholder();
    }

    public function getInitiatorId()
    {
        $connection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $sql = 'SELECT `uid` FROM `gaussdev_like` WHERE `id`=?';

        return $connection->fetchOne($sql, $this->getNotification()->getDataId());
    }
}