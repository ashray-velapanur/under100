<?php

/**
 * Class GaussDev_Notifications_Model_Type_Comment_Replied
 *
 * data_id table field - gaussdev_comments.entity_id
 */
class GaussDev_Notifications_Model_Type_Comment_Replied extends GaussDev_Notifications_Model_Type_Abstract
{
    public function getText()
    {
        $text = Mage::helper('core')->__('%s has replied to your comment.', Mage::helper('notifications')->getName($this->getInitiator()));

        return $text;
    }

    public function getImage()
    {
        return $this->getInitiator()->getProfileImage() ? Mage::helper('gaussdev_customerimages')->getUrl($this->getInitiator()) :
            $this->_getPlaceholder();
    }

    public function getInitiatorId()
    {
        $dataModel = $this->getDataModel();
        if ($dataModel) {
            return $dataModel->getCustomerId();
        } else {
            return false;
        }
    }

    /**
     * @return GaussDev_Comments_Model_Comment|false
     */
    protected function getDataModel()
    {
        return Mage::getModel('gaussdev_comments/comment')->load($this->getNotification()->getDataId());
    }
}