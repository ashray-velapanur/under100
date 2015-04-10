<?php

class GaussDev_Parser_ProductsController extends Mage_Core_Controller_Front_Action
{
    protected $_mimeTypes = array(
        'image/jpeg' => 'jpg',
        'image/gif'  => 'gif',
        'image/png'  => 'png'
    );

    public function createAction()
    {
        try {
            $this->_redirect(null, array('_direct' => 'profile', '_fragment' => 'posts'));
            $product = $this->_create();
            if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                $this->_redirectUrl($product->getProductUrl());
            }
        } catch (Exception $e) {
        }
    }

    public function createfrombookAction()
    {
        try {
            $this->_create();
        } catch (Exception $e) {
        }
        $this->getResponse()->setBody('<script>window.close()</script>');
    }


    public function addImageFromUrlAction()
    {
        $url = $this->getRequest()->getPost('img_url');
        $response = json_encode(Mage::helper('gaussdev_parser')->downloadImage(null, $url));
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody($response);
    }

    public function uploadImageAction()
    {
        $data = new Varien_Object;
        $uploadPaths = array();
        $images = isset($_FILES['images']['name']) ? $_FILES['images'] : array();
        foreach ($images['name'] as $i => $file) {
            $dir = Mage::helper('gaussdev_parser')->tmpdir();
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            $ext = pathinfo($images['name'][$i], PATHINFO_EXTENSION);
            do {
                $tmpFile = hash('sha256',
                        mt_rand() . uniqid(substr(base64_encode(file_get_contents($images['tmp_name'][$i])), 0, 100),
                            true)) . '.' . $ext;
                $uploadPath = $dir . DS . $tmpFile;
            } while (file_exists($uploadPath));
            if (preg_match('/(' . GaussDev_Parser_Model_Parser::VALID_FORMATS . ')$/i', $ext)
                && array_key_exists($images['type'][$i], $this->_mimeTypes)
            ) {
                move_uploaded_file($images['tmp_name'][$i], $uploadPath);
                $uploadPaths[] = str_replace(Mage::getBaseDir() . DS,
                    Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB), $uploadPath);
            }
        }
        $data->setImages($uploadPaths);
        $block = $this->getLayout()
                      ->createBlock('gaussdev_parser/bookmarklet')
                      ->setParsed($data)
                      ->setChecked('checked')
                      ->setTemplate('parser/bookmarklet-local.phtml');

        $this->getResponse()->setBody($block->toHtml());
    }

    public function parseProductAction()
    {
        $url = $this->getRequest()->getParam('url');
        $data = Mage::getModel('gaussdev_parser/parser')->parse($url)->getData();
        $this->getResponse()->setBody(Zend_Json::encode($data));
    }

    public function createProductAction()
    {
        $this->_create_parser_product();
    }

    public function parseAction()
    {
        $data = new Varien_Object;

        try {
            $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
            if (!$customerId) {
                throw new Exception('Not logged in.');
            }
            $url = $this->getRequest()->getParam('url');
            if ($url) {
                $data = Mage::getModel('gaussdev_parser/parser')->parse($url);
            }
        } catch (Exception $e) {
            $product = Mage::registry('parsed_product');
            if ($product) {
                $this->getResponse()->setHeader('product-url-redirect', $product->getProductUrl());
                $this->getResponse()->sendHeadersAndExit();
            }
        }
        $block = $this->getLayout()
                      ->createBlock('gaussdev_parser/bookmarklet')
                      ->setParsed($data)
                      ->setTemplate('parser/bookmarklet.phtml');

        $this->getResponse()->setBody($block->toHtml());
    }

    public function parsefrombookAction()
    {
        $isLoggedIn = Mage::getSingleton('customer/session')->isLoggedIn();

        if ($isLoggedIn) {
            $url = $this->getRequest()->getParam('url');
            if ($url) {
                try {
                    $data = Mage::getModel('gaussdev_parser/parser')->parse($url);
                    $this->loadLayout();
                    $this->getLayout()->getBlock('bookmarklet')->setParsed($data)->setBook(true);
                    $this->renderLayout();
                } catch (Exception $e) {
                    Mage::helper('gaussdev')->log($e);
                }
            }
        } else {
            die('Please log in.');
        }
    }

    private function _create()
    {
        if (!$this->_validateFormKey()) {
            throw new Exception('Invalid Form Key.');
        }
        $customerId = Mage::getSingleton('customer/session')->getCustomerId();
        if (!$customerId) {
            throw new Exception('Customer is not logged in');
        }

        return $this->_create_parser_product();
    }

    private function _create_parser_product()
    {
        $customerId = $this->getRequest()->getPost('customer_id');
        $price = $this->getRequest()->getPost('price');
        $description = $this->getRequest()->getPost('description');
        $productName = $this->getRequest()->getPost('name');
        $categoryIds = $this->getRequest()->getPost('categoryIds');
        $brand = $this->getRequest()->getPost('brand');
        $productUrl = $this->getRequest()->getPost('url');
        $listId = $this->getRequest()->getPost('listId');
        $images = $this->getRequest()->getPost('images');
        $nameXpath = $this->getRequest()->getPost('name_xpath');
        $priceXpath = $this->getRequest()->getPost('price_xpath');

        $product = Mage::getModel('gaussdev_parser/product');
        $product->setData('price', $price)
                ->setData('description', $description)
                ->setData('name', $productName)
                ->setData('categoryIds', $categoryIds)
                ->setData('brand', $brand)
                ->setData('url', $productUrl)
                ->setData('customer_id', $customerId)
                ->setData('listId', $listId)
                ->setData('images', $images)
                ->setData('name_xpath', $nameXpath)
                ->setData('price_xpath', $priceXpath);
        return $product->save();
    }
}