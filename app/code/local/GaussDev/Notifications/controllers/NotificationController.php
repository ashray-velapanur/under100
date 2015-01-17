<?php

class GaussDev_Notifications_NotificationController extends Mage_Core_Controller_Front_Action
{
    public function saveAction()
    {
        try {
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode(false));
            $id = $this->getRequest()->getParam('id');
            if (!$id) {
                throw new Exception('Invalid request.');
            }
            Mage::getModel('notifications/notification')->load($id)->setIsSaved(1)->save();
            $this->getResponse()->setBody(json_encode(true));
        } catch (Exception $e) {
        }
    }

    public function deleteAction()
    {
        try {
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(json_encode(false));
            $id = $this->getRequest()->getParam('id');
            if (!$id) {
                throw new Exception('Invalid request.');
            }
            Mage::getModel('notifications/notification')->load($id)->delete();
            $this->getResponse()->setBody(json_encode(true));
        } catch (Exception $e) {
        }
    }
}