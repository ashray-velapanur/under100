<?php

class GaussDev_Parser_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    public function create($customerId, $productInfo, $nameXpath = null, $priceXpath = null)
    {
        $product_name = null;
        $product_price = null;
        $product_brand = null;
        $product_description = null;
        $product_url = null;
        $product_category_ids = array();
        $product_images = array();
        $product_list_id = null;

        extract($productInfo, EXTR_PREFIX_ALL, 'product');

        if (empty($customerId)) {
            $this->_fault('data_invalid', 'Customer ID is required');
        }

        $product = Mage::getModel('gaussdev_parser/product');
        $product->setData('price', $product_price)
                ->setData('description', $product_description)
                ->setData('name', $product_name)
                ->setData('categoryIds', $product_category_ids)
                ->setData('brand', $product_brand)
                ->setData('url', $product_url)
                ->setData('customer_id', $customerId)
                ->setData('listId', $product_list_id)
                ->setData('images', $product_images)
                ->setData('name_xpath', $nameXpath)
                ->setData('price_xpath', $priceXpath);
        $product->save();

        return true;
    }

    public function parse_url($url)
    {
        $response = array();
        try {
            $response = Mage::getModel('gaussdev_parser/parser')->parse($url)->getData();

        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
            $this->_fault('data_invalid', $e->getMessage());
        }

        return $response;
    }

    public function get_image_from_url($url)
    {
        $downloadedImage = null;
        try {
            $downloadedImage = Mage::helper('gaussdev_parser')->downloadImage(null, $url);
            if (!$downloadedImage) {
                throw new Exception('Invalid image URL.');
            }
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
            $this->_fault('data_invalid', $e->getMessage());
        }

        return $downloadedImage;
    }

    public function upload_image($base64image, $mimeType)
    {
        try {
            $dir = Mage::helper('gaussdev_parser')->tmpdir();

            $mimeTypes = array('image/jpeg' => 'jpg', 'image/gif' => 'gif', 'image/png' => 'png');
            $fileContent = @base64_decode($base64image, true);
            if (!$fileContent || !array_key_exists($mimeType, $mimeTypes)) {
                return false;
            }
            do {
                $fileName = base_convert(md5(uniqid() . mt_rand()), 16, 35) . '.' . $mimeTypes[strtolower($mimeType)];
                $path = $dir . Varien_File_Uploader::getDispretionPath($fileName);
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

            $imageFile = str_replace(Mage::getBaseDir() . DS, Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
                $file);

            return $imageFile;
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
            $this->_fault('data_invalid', $e->getMessage());
        }

        return null;
    }

    public function create_from_url($customerId, $productUrl)
    {
        try {
            Mage::helper('gaussdev_parser')->createFromUrl($productUrl, $customerId);
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
            $this->_fault('data_invalid', $e->getMessage());
        }

        return true;
    }

    public function get_brands()
    {
        $attributeCode = 'brand';
        $brands = Mage::getResourceModel('catalog/product_collection')
                      ->groupByAttribute($attributeCode)
                      ->addAttributeToFilter('status', '1')
                      ->addAttributeToFilter($attributeCode, array('notnull' => true))
                      ->addAttributeToFilter($attributeCode, array('neq' => ''))
                      ->addAttributeToSelect($attributeCode)
                      ->addFilterByRequiredOptions()
                      ->getColumnValues($attributeCode);

        $brands = array_map('trim', $brands);
        $brands = array_unique($brands, SORT_STRING);
        natcasesort($brands);

        return array_values($brands);
    }

}