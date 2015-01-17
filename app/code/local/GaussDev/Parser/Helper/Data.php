<?php

class GaussDev_Parser_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_mimeTypes = array(
        'image/jpeg' => 'jpg',
        'image/gif'  => 'gif',
        'image/png'  => 'png'
    );

    public function downloadImage($dirstring, $url)
    {
        $url = $this->reconstructUrl($url);
        $urlRegex = '/(?<url>.*\.(' . GaussDev_Parser_Model_Parser::VALID_FORMATS . '))/i';
        preg_match($urlRegex, $url, $imageUrl);
        $imageUrl = isset($imageUrl['url']) ? $imageUrl['url'] : null;
        if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            return false;
        }
        $image = $this->getUrlContents($imageUrl);
        $dir = Mage::helper('gaussdev_parser')->tmpdir() . DS . substr(base64_encode(md5($dirstring)), 0, 10);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $file_info = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $file_info->buffer($image);
        preg_match('/\.(' . GaussDev_Parser_Model_Parser::VALID_FORMATS . ')/i', $imageUrl, $extension);
        $extension = isset($this->_mimeTypes[strtolower($mime_type)]) ? $this->_mimeTypes[strtolower($mime_type)]
            : $extension;
        $extension = (array)$extension;
        if (empty($extension)) {
            return false;
        }
        $filepath = $dir . DS . substr(base64_encode(md5($imageUrl)), 0, 10) . '.' . $extension[0];
        if (file_exists($filepath)) {
            $saved = true;
        } else {
            $saved = file_put_contents($filepath, $image);
        }
        if ($saved && @getimagesize($filepath)) {
            return str_replace(Mage::getBaseDir() . DS, Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
                $filepath);
        } else {
            return false;
        }
    }

    public function getUrlContents($url)
    {
        $ua = 'Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0';
        $headers = array(
            "User-Agent: {$ua}",
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Charset: utf-8',
            'Accept-Language: en-US,en;q=0.5',
        );
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, '');
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }

    public function tmpdir()
    {
        return Mage::getBaseDir('media') . DS . 'tmp' . DS . 'uploads';
    }

    public function makeSku($customerId, $name)
    {
        if (empty($name)) {
            throw new Exception('Product name is required.');
        }
        if (empty($customerId) || !is_numeric($customerId)) {
            throw new Exception('Invalid customer ID.');
        }
        $hash = base_convert(md5($name), 16, 36);
        $customerId = base_convert($customerId, 10, 36);
        $maxLength = Mage_Catalog_Model_Product_Attribute_Backend_Sku::SKU_MAX_LENGTH;
        $hash = substr($hash, 0, ($maxLength - strlen($customerId) - 1));
        $sku = "{$hash}/{$customerId}";

        return $sku;
    }


    /**
     * @param $productUrl
     * @param $customerId
     *
     * @return bool
     * @throws Exception
     */
    public function createFromUrl($productUrl, $customerId)
    {
        if (empty($productUrl) || !filter_var($productUrl, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL provided.');
        }

        if (empty($customerId)) {
            throw new Exception('No customer ID provided.');
        }

        $productExists = Mage::getModel('catalog/product')
                             ->getCollection()
                             ->addFieldToFilter('product_owner_id', $customerId)
                             ->addFieldToFilter('product_origin_url', $productUrl)
                             ->count();

        if ($productExists) {
            throw new Exception('Product already exists.', 1);
        }

        $entityTypeId = Mage::getModel('eav/entity')->setType('catalog_product')->getTypeId();
        $attribute_set_id = Mage::getModel("eav/entity_attribute_set")
                                ->getCollection()
                                ->setEntityTypeFilter($entityTypeId)
                                ->addFieldToFilter('attribute_set_name', 'Customer Product')
                                ->fetchItem()
                                ->getAttributeSetId();
        $product = Mage::getModel('catalog/product');
        $product->setWebsiteIds(array(1))
                ->setAttributeSetId($attribute_set_id)
                ->setTypeId('simple')
                ->setCreatedAt(strtotime('now'))
                ->setWeight(0)
                ->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED)
                ->setTaxClassId(2)
                ->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH)
                ->setIsVerified(false)
                ->setProductOwnerId($customerId)
                ->setProductOriginUrl($productUrl)
                ->setStockData(array('use_config_manage_stock' => 1))
                ->save();
        Mage::dispatchEvent('customer_product_create', array('product' => $product));

        return (bool)$product->getId();

    }

    public function generateBookmarklet()
    {
        $url = $this->_getUrl('customer_products/products/parsefrombook');

        return "javascript:(function(){window.open('{$url}?url='+encodeURIComponent(document.URL), '_blank', 'scrollbars=yes,location=0,menubar=0,height=640,width=510');})();";
    }

    public function getOwner($customerId)
    {
        if (!$customerId) {
            return 'Under100';
        }
        $customer = Mage::getModel('customer/customer')->load($customerId);

        return $customer->getUsername() ?: ($customer->getFirstname() ?: $customer->getLastname());
    }

    public function getOwnerHtml($customerId, $class = null)
    {
        $url = Mage::getUrl(null, array('_direct' => 'profile', '_query' => array('id' => $customerId)));
        $owner = $this->escapeHtml($this->getOwner($customerId));
        $str = "<a class=\"{$class}\" href=\"{$url}\">{$owner}</a>";

        return $str;
    }

    public function productsCount($id = null)
    {
        $collection = $this->getCollection($id);

        return $collection->getSize();
    }

    public function getCollection($id = null)
    {
        $id = $id ?: Mage::getSingleton('customer/session')->getCustomerId();
        $collection = Mage::getResourceSingleton('catalog/product_collection')
                          ->addAttributeToFilter('product_owner_id', $id)
                          ->addAttributeToSelect('*');

        return $collection;
    }

    public function reconstructUrl($url)
    {
        $constructed_url = '';
        $url_parts = parse_url($url);
        if ($url_parts) {
            $constructed_url = $url_parts['scheme'] . '://' . $url_parts['host'] . (isset($url_parts['path'])
                    ? $url_parts['path'] : '');
        }

        return $constructed_url;
    }
}
