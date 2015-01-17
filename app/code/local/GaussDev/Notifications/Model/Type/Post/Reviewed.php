<?php

/**
 * Class GaussDev_Notifications_Model_Type_Post_Reviewed
 *
 * data_id table field - review.review_id
 */
class GaussDev_Notifications_Model_Type_Post_Reviewed extends GaussDev_Notifications_Model_Type_Abstract
{
    public function getText()
    {
        $text = Mage::helper('core')->__('%s has reviewed your post.', Mage::helper('notifications')->getName($this->getInitiator()));

        return $text;
    }

    public function getImage()
    {
        return $this->getInitiator()->getProfileImage() ? Mage::helper('gaussdev_customerimages')->getUrl($this->getInitiator()) :
            $this->_getPlaceholder();
    }

    public function getInitiatorId()
    {
        return $this->getDataModel()->getCustomerId();
    }

    /**
     * @return Mage_Review_Model_Review|false
     */
    protected function getDataModel()
    {
        return Mage::getModel('review/review')->load($this->getNotification()->getDataId());
    }
}