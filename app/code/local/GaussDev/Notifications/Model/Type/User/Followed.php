<?php

/**
 * Class GaussDev_Notifications_Model_Type_User_Followed
 *
 * data_id table field - gaussdev_follow.id
 */
class GaussDev_Notifications_Model_Type_User_Followed extends GaussDev_Notifications_Model_Type_Abstract
{
    public function getText()
    {
        $text = Mage::helper('core')->__('%s is now following you.', Mage::helper('notifications')->getName($this->getInitiator()));

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
        $sql = 'SELECT `uid` FROM `gaussdev_follow` WHERE `id`=?';

        return $connection->fetchOne($sql, $this->getNotification()->getDataId());
    }
}