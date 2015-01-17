<?php

class GaussDev_Notifications_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    public function getAll($customerId)
    {
        $data = array();
        $collection = Mage::getResourceModel('notifications/notification_collection')->addFieldToFilter('notify_id', $customerId);
        /** @var GaussDev_Notifications_Model_Notification $_notification */
        foreach ($collection as $_notification) {
            $data[] = array(
                'entity_id' => $_notification->getId(),
                'text'      => $_notification->getRenderedText(),
                'image'     => $_notification->getRenderedImageUrl(),
                'is_saved'  => $_notification->getIsSaved()
            );
        }

        return $data;
    }

    public function save($notificationId)
    {
        Mage::getModel('notifications/notification')->load($notificationId)->setIsSaved(1)->save();

        return true;
    }

    public function delete($notificationId)
    {
        Mage::getModel('notifications/notification')->load($notificationId)->delete();

        return true;
    }

    public function count($customerId)
    {
        return Mage::getResourceModel('notifications/notification_collection')->addFieldToFilter('is_saved', 0)->addFieldToFilter(
            'notify_id',
            $customerId
        )->getSize();
    }

    public function generate($notifyId, $count = 1)
    {
        $collection = Mage::getResourceModel('gaussdev_comments/comment_collection')->setPageSize($count);
        $collection->getSelect()->order(new Zend_Db_Expr('RAND()'));
        foreach ($collection as $_comment) {
            $notification = Mage::getModel('notifications/notification')->setType('comment_replied')->setNotifyId($notifyId)->setDataId(
                    $_comment->getId()
                )->save();
            $data[] = array($notification->getRenderedText(), $notification->getRenderedImageUrl());
        }

        return isset($data) ? $data : null;
    }

    public function registerAndroidDevice($customerId, $deviceId)
    {
        if (!$customerId || !$deviceId) {
            return false;
        }
        /** @var GaussDev_Notifications_Model_Device $model */
        $model = Mage::getResourceModel('notifications/device_collection')->addFieldToFilter('device_id', $deviceId)->addFieldToFilter(
            'type',
            'android'
        )->getFirstItem();
        $model->setCustomerId($customerId)->setDeviceId($deviceId)->setType('android')->save();

        return true;
    }

    public function unregisterAndroidDevice($deviceId)
    {
        if (!$deviceId) {
            return false;
        }
        /** @var GaussDev_Notifications_Model_Device $model */
        Mage::getResourceModel('notifications/device_collection')->addFieldToFilter('device_id', $deviceId)->addFieldToFilter(
            'type',
            'android'
        )->getFirstItem()->delete();

        return true;
    }

    public function registerIosDevice($customerId, $deviceId)
    {
        if (!$customerId || !$deviceId) {
            return false;
        }
        /** @var GaussDev_Notifications_Model_Device $model */
        $model = Mage::getResourceModel('notifications/device_collection')->addFieldToFilter('device_id', $deviceId)->addFieldToFilter(
            'type',
            'ios'
        )->getFirstItem();
        $model->setCustomerId($customerId)->setDeviceId($deviceId)->setType('ios')->save();

        return true;
    }

    public function unregisterIosDevice($deviceId)
    {
        if (!$deviceId) {
            return false;
        }
        /** @var GaussDev_Notifications_Model_Device $model */
        Mage::getResourceModel('notifications/device_collection')->addFieldToFilter('device_id', $deviceId)->addFieldToFilter(
            'type',
            'ios'
        )->getFirstItem()->delete();

        return true;
    }
}