<?php

class GaussDev_Parser_Model_Observer
{
    public function createdProduct(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->_sendStatusMail($product);

        return $this;
    }

    private function _sendStatusMail(Mage_Catalog_Model_Product $product)
    {
        try {
            $emailTemplate = Mage::getModel('core/email_template');

            $emailTemplate->loadDefault('customer_product_create_tpl');
            $emailTemplate->setTemplateSubject('Your order was holded');

            $salesData['email'] = Mage::getStoreConfig('trans_email/ident_general/email');
            $salesData['name'] = Mage::getStoreConfig('trans_email/ident_general/name');

            $enabled = (bool)Mage::getStoreConfig('customer_products/email_notifications/emails_enabled');
            $emails = array_map('trim',
                explode(',', Mage::getStoreConfig('customer_products/email_notifications/emails')));

            $emailTemplate->setSenderName($salesData['name']);
            $emailTemplate->setSenderEmail($salesData['email']);

            $emailTemplateVariables['product_id'] = $product->getId();
            $emailTemplateVariables['product_sku'] = $product->getSku();
            $emailTemplateVariables['product_name'] = $product->getName();
            if ($emails && $enabled == true && $product->getId()) {
                $emailTemplate->send($emails, null, $emailTemplateVariables);
            }
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
        }
    }

    public function deleteOldTemporaryFiles()
    {
        $dir = Mage::helper('gaussdev_parser')->tmpdir();
        $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
        $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);

        foreach ($ri as $file) {
            if ($file->isFile() && ((time() - filemtime($file)) >= (1 * 3600))) {
                unlink($file);
            }
            if ($file->isDir() && count(glob($file . DS . "*")) === 0) {
                rmdir($file);
            }
        }

        return $this;
    }

    public function hashExistingProductsOriginPages()
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
                          ->addAttributeToFilter('product_origin_page_hash', array('null' => true, 'eq' => ''), 'left')
                          ->addAttributeToFilter('product_origin_url', array('notnull' => true, 'neq' => ''), 'left');
        Mage::getResourceSingleton('cataloginventory/stock')->setInStockFilterToCollection($collection);
        foreach ($collection as $_product) {
            $urlContents = null;
            $hash = null;
            try {
                $url = $_product->getData('product_origin_url');
                $urlContents = Mage::helper('gaussdev_parser')->getUrlContents($url);
                $hash = sha1($urlContents);
                $_product->setData('product_origin_page_hash', $hash)->save();
            } catch (Exception $e) {
            }
            try {
                if (!$urlContents || !$hash) {
                    $_product->setStatus(Mage_Catalog_Model_Product_Status::STATUS_DISABLED)->save();
                }
            } catch (Exception $e) {
            }
        }

        return $this;
    }

}