<?php

class GaussDev_Parser_Model_Product extends Varien_Object
{

    /**
     * @throws Exception
     */
    public function save()
    {
        Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        $entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();

        $attribute_set_id = Mage::getModel("eav/entity_attribute_set")
                                ->getCollection()
                                ->setEntityTypeFilter($entityTypeId)
                                ->addFieldToFilter('attribute_set_name', 'Customer Product')
                                ->fetchItem()
                                ->getAttributeSetId();

        $price = $this->getData('price');
        $description = $this->getData('description');
        $productName = $this->getData('name');
        $categoryIds = $this->getData('categoryIds');
        $brand = $this->getData('brand');
        $productUrl = $this->getData('url');
        $customerId = $this->getData('customer_id');
        $listId = $this->getData('listId');
        $images = $this->getData('images');

        $status = Mage_Catalog_Model_Product_Status::STATUS_ENABLED;
        if ($productUrl && !filter_var($productUrl, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)) {
            $status = Mage_Catalog_Model_Product_Status::STATUS_DISABLED;
        }
        $hash = $productUrl ? sha1(Mage::helper('gaussdev_parser')->getUrlContents($productUrl)) : null;
        $sku = Mage::helper('gaussdev_parser')->makeSku($customerId, $productName);

        /** @var Mage_Catalog_Model_Product $product */
        $product = Mage::getModel('catalog/product')
                       ->setWebsiteIds(array(1))
                       ->setAttributeSetId($attribute_set_id)
                       ->setTypeId('simple')
                       ->setCreatedAt(strtotime('now'))
                       ->setSku($sku)
                       ->setName($productName)
                       ->setWeight(0)
                       ->setPrice($price)
                       ->setBrand($brand)
                       ->setStatus($status)
                       ->setTaxClassId(2)
                       ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                       ->setIsVerified('0')
                       ->setDescription($description)
                       ->setProductOwnerId($customerId)
                       ->setProductOriginUrl($productUrl)
                       ->setProductOriginPageHash($hash)
                       ->setMediaGallery(array('images' => array(), 'values' => array()))
                       ->setStockData(array(
                           'use_config_manage_stock' => 1,
                           'qty'                     => '99999999',
                           'is_in_stock'             => 1
                       ))
                       ->setCategoryIds((array)$categoryIds);

        $i = 1;
        foreach ($images as $image) {
            try {
                $image = str_replace(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), Mage::getBaseDir() . DS,
                    $image);
                if (file_exists($image)) {
                    $product->addImageToMediaGallery($image, $i ? array('thumbnail', 'small_image', 'image') : null,
                        true, false);
                    $i = 0;
                }
            } catch (Exception $e) {
            }
        }
        $oldStoreId = Mage::app()->getStore()->getId();
        $product->save();
        $this->_saveXpath($productUrl, $price);
        Mage::app()->setCurrentStore($oldStoreId);
        Mage::helper('GaussDev_Multilist')->additem($listId, $product->getId());
        Mage::dispatchEvent('customer_product_create', array('product' => $product));


        return $product;

    }

    private function _saveXpath($url, $price)
    {
        try {
            $data = Mage::getSingleton('core/session')->getData('price_xpath_' . md5($url));
            if ($data) {
                $xpathArray = unserialize(base64_decode($data));
                foreach ((array)$xpathArray as $xpath) {
                    if (number_format($xpath['price'], 2) == number_format($price, 2)) {
                        Mage::getModel('gaussdev_parser/host')
                            ->setHostname(parse_url($url, PHP_URL_HOST))
                            ->setPriceXpath($xpath['xpath'])
                            ->save();
                        break;
                    }
                }
            }
        } catch (Exception $e) {
        }
    }
}