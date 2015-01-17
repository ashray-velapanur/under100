<?php

class GaussDev_CustomerImages_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_mimeTypes = array(
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'png'  => 'image/png',
    );
    /* @var GaussDev_CustomerImages_Model_Config */
    private $_config;

    public function __construct()
    {
        $this->_config = Mage::getModel('gaussdev_customerimages/config');
    }

    /**
     * @param Mage_Customer_Model_Customer $customer
     *
     * @param                              $fileContent
     * @param                              $mime
     * @param null                         $ext
     *
     * @throws Exception
     * @return bool
     */
    public function changeImage(Mage_Customer_Model_Customer $customer, $fileContent, $mime, $ext = null)
    {
        if ($mime && is_string($mime) && in_array(strtolower($mime), $this->_mimeTypes)) {
            $extension = array_search(strtolower($mime), $this->_mimeTypes);
        } elseif ($ext && is_string($ext) && array_key_exists(strtolower($ext), $this->_mimeTypes)) {
            $extension = strtolower($ext);
        } else {
            return false;
        }
        do {
            $fileName = base_convert(md5(uniqid() . mt_rand()), 16, 35) . '.' . $extension;
            $attrPath = Mage::getBaseDir('media') . DS . 'customer';
            $path = $attrPath . Varien_File_Uploader::getDispretionPath($fileName);
            $file = $path . DS . $fileName;
        } while (file_exists($file));

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->checkAndCreateFolder($path);
        $ioAdapter->open(array('path' => $path));
        $ioAdapter->write($fileName, $fileContent, 0666);
        unset($fileContent);
        try {
            new Varien_Image($file);
        } catch (Exception $e) {
            $ioAdapter->rm($path);

            throw $e;
        }

        $original = $customer->getProfileImage();
        $toDelete = (bool)$original;

        if ($toDelete) {
            $customer->setProfileImage('');
            $oldFile = $attrPath . $original;
            $ioFile = new Varien_Io_File();
            if ($ioFile->fileExists($oldFile)) {
                $ioFile->rm($oldFile);
            }
        }

        $imageFile = str_replace($attrPath, '', $file);

        $customer->setProfileImage($imageFile);

        $customer->save();

        return true;
    }

    public function getUrl($customerId = null)
    {
        $file = $this->_getFile($customerId);
        if ($file) {
            return $this->_config->getMediaUrl($file);
        } else {
            return $this->getPlaceholder();
        }
    }

    private function _getFile($customer)
    {
        $file = null;
        if ($customer) {
            if (is_numeric($customer)) {
                $customer = Mage::getModel('customer/customer')->load($customer);
            } elseif (!(is_object($customer) && $customer instanceof Mage_Customer_Model_Customer)) {
                return false;
            }
            $file = $customer->getProfileImage() ?: $file;
        } elseif (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $file = Mage::getSingleton('customer/session')->getCustomer()->getProfileImage() ?: $file;
        } else {
            return false;
        }

        if (file_exists($this->_config->getBaseMediaPath() . $file)) {
            return $file;
        } else {
            return false;
        }
    }

    public function getPlaceholder()
    {
        return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN, true) . 'frontend/under/default/images/catalog/product/placeholder/avatar-placeholder-62.png';
    }
}
