<?php

class GaussDev_CustomerImages_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    protected $_mimeTypes = array(
        'image/jpeg' => 'jpg',
        'image/gif'  => 'gif',
        'image/png'  => 'png'
    );

    public function upload($customerId, $image, $mime)
    {
        try {
            $customer = Mage::getModel('customer/customer')->load($customerId);

            if (!isset($image, $mime)) {
                $this->_fault('data_invalid', Mage::helper('catalog')->__('The image is not specified.'));
            }

            if (!isset($this->_mimeTypes[$mime])) {
                $this->_fault('data_invalid', Mage::helper('catalog')->__('Invalid image type.'));
            }

            $fileContent = base64_decode($image, true);
            if (!$fileContent) {
                $this->_fault('data_invalid',
                    Mage::helper('catalog')->__('The image contents is not valid base64 data.'));
            }

            unset($image);

            Mage::helper('gaussdev_customerimages')->changeImage($customer, $fileContent, $mime);

            return Mage::helper('gaussdev_customerimages')->getUrl($customer->getId());
        } catch (Exception $e) {
            $this->_fault('data_invalid', $e->getMessage());
        }

        return false;
    }

    public function get($customerId)
    {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $facebookId = $customer->getFacebookId();
        $imageUrl = Mage::helper('gaussdev_customerimages')->getUrl($customerId);
        return array('facebookId'=>$facebookId, 'imageUrl'=>$imageUrl);
    }

}