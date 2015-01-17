<?php

/**
 * @method GaussDev_Notifications_Model_Notification setEntityId(int $value)
 * @method int getNotifyId()
 * @method GaussDev_Notifications_Model_Notification setNotifyId(int $value)
 * @method int getIsSaved()
 * @method GaussDev_Notifications_Model_Notification setIsSaved(int $value)
 * @method string getType()
 * @method GaussDev_Notifications_Model_Notification setType(string $value)
 * @method int getDataId()
 * @method GaussDev_Notifications_Model_Notification setDataId(int $value)
 * @method string getCreatedAt()
 * @method GaussDev_Notifications_Model_Notification setCreatedAt(string $value)
 * @method string getRenderedText()
 * @method GaussDev_Notifications_Model_Notification setRenderedText(string $value)
 * @method string getRenderedImageUrl()
 * @method GaussDev_Notifications_Model_Notification setRenderedImageUrl(string $value)
 */
class GaussDev_Notifications_Model_Notification extends Mage_Core_Model_Abstract
{

    protected $_eventPrefix = 'notification';
    protected $_typeModel;

    protected $_validTypes = array('user_followed', 'comment_replied', 'post_commented', 'post_liked', 'post_reviewed', 'post_purchased');

    /**
     * @return GaussDev_Notifications_Model_Type_Abstract|Varien_Object
     */
    public function getTypeModel()
    {
        if ($this->_typeModel) {
            return $this->_typeModel;
        }
        if (!$this->_hasValidType()) {
            return new Varien_Object;
        }
        $type = $this->getType();
        $modelString = 'notifications/type_' . $type;
        $model = Mage::getModel($modelString);
        if ($model) {
            $model->setNotification($this);
        } else {
            return new Varien_Object;
        }

        $this->_typeModel = $model;

        return $model;
    }

    protected function _hasValidType()
    {
        return in_array($this->getType(), $this->_validTypes);
    }

    protected function _construct()
    {
        $this->_init('notifications/notification');
    }

    protected function _beforeSave()
    {
        if (!$this->_hasValidType()) {
            throw new Exception('Invalid type.');
        }

        if ($this->getTypeModel()->getInitiatorId() == $this->getNotifyId()) {
            $this->_dataSaveAllowed = false;
        }

        switch ($this->getType()) {
            case 'user_followed':
                if ($this->isObjectNew() && !$this->_isUnique()) {
                    $this->_dataSaveAllowed = false;
                }
                break;
            case 'post_liked':
                if ($this->isObjectNew() && !$this->_isUnique()) {
                    $this->_dataSaveAllowed = false;
                }
                break;
        }

        return parent::_beforeSave();
    }

    protected function _isUnique()
    {
        $collection = Mage::getResourceModel('notifications/notification_collection')
                          ->addFieldToFilter('notify_id', $this->getNotifyId())
                          ->addFieldToFilter('type', $this->getType())
                          ->addFieldToFilter('is_saved', 0)
                          ->setCurPage(1);

        $return = !(bool)$collection->getFirstItem()->getId();

        return $return;
    }
}