<?php

class GaussDev_Notifications_Model_Observer
{

    public function testAndroidPush($notification) { $this->_pushAndroid($notification); }

    public function testIosPush($notification) { $this->_pushIos($notification); }

    public function sendPush()
    {
        /** @var GaussDev_Notifications_Model_Notification $notification */
        $notifications = Mage::getResourceModel('notifications/notification_collection')->addFieldToFilter('is_push_sent', 0)->addFieldToFilter(
            'created_at',
            array('to' => '-1 minutes', 'date' => true)
        );
        foreach ($notifications as $notification) {
            try {
                $this->_pushAndroid($notification);
                $this->_pushIos($notification);

                $notification->setIsPushSent(1)->save();
            } catch (Exception $e) {
            }
        }

        return $this;
    }

    private function _pushAndroid($notification)
    {
        /** @var GaussDev_Notifications_Model_Notification $notification */
        try {
            $devices = Mage::getResourceModel('notifications/device_collection')
                           ->addFieldToFilter('customer_id', $notification->getNotifyId())
                           ->addFieldToFilter('type', 'android');
            if ($devices->getSize() === 0) {
                return;
            }

            $text = $notification->getRenderedText();
            $deviceIds = $devices->getColumnValues('device_id');

            $res = Mage::helper('notifications')->sendGcm($deviceIds, array('message' => $text));

            foreach ($res->results as $_key => $_result) {
                $_device = $devices->getItemByColumnValue('device_id', $deviceIds[$_key]);

                if ($_device === null) {
                    continue;
                }

                if (isset($_result->registration_id) && $_result->registration_id !== $deviceIds[$_key]) {
                    $_device->setDeviceId($_result->registration_id)->save();
                }
                if (isset($_result->error)) {
                    if ($_result->error === 'Unavailable') {
                        Mage::helper('notifications')->sendGcm(array($deviceIds[$_key]), array('message' => $text));
                    } elseif ($_result->error === 'NotRegistered' || $_result->error === 'InvalidRegistration') {
                        $devices->getItemByColumnValue('device_id', $deviceIds[$_key])->delete();
                    }
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return;
    }

    private function _pushIos($notification)
    {
        /** @var GaussDev_Notifications_Model_Notification $notification */
        try {
            $devices = Mage::getResourceModel('notifications/device_collection')
                           ->addFieldToFilter('customer_id', $notification->getNotifyId())
                           ->addFieldToFilter('type', 'ios');
            if ($devices->getSize() === 0) {
                return;
            }

            $text = $notification->getRenderedText();

            foreach ($devices as $_device) {

                try {
                    Mage::helper('notifications')->sendApn($_device->getDeviceId(), $text);
                } catch (Zend_Mobile_Push_Exception_InvalidToken $e) {
                    $_device->delete();
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function itemPurchased(Varien_Event_Observer $observer)
    {
        try {
            $orderIds = $observer->getOrderIds();
            if (empty($orderIds) || !is_array($orderIds)) {
                return $this;
            }
            $orderId = $orderIds[0];
            /** @var Mage_Sales_Model_Order $order */
            $order = Mage::getModel('sales/order')->load($orderId);
            $productIds = $order->getItemsCollection()->getColumnValues('product_id');
            $productsCollection = Mage::getResourceModel('catalog/product_collection')
                                      ->addAttributeToFilter('entity_id', array('in' => $productIds))
                                      ->addAttributeToSelect('*');
            foreach ($productsCollection as $_product) {
                $notifyId = $_product->getProductOwnerId();
                if ($notifyId) {
                    try {
                        Mage::getModel('notifications/notification')
                            ->setType('post_purchased')
                            ->setNotifyId($notifyId)
                            ->setDataId($_product->getId())
                            ->save();
                    } catch (Exception $e) {
                    }
                }
            }
        } catch (Exception $e) {
        }

        return $this;
    }
}