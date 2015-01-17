<?php

class GaussDev_Core_Helper_Data extends Mage_Core_Helper_Abstract
{

    private $categories;

    public function log(Exception $e)
    {
        Mage::log("\n" . $e->__toString(), Zend_Log::ERR, 'GaussDev.log');
    }

    public function getCategories($rootCatId)
    {
        $this->categories = Mage::getModel('catalog/category')
                                ->getCollection()
                                ->addIsActiveFilter()
                                ->addAttributeToSelect('*')
                                ->addAttributeToFilter('entity_id', array('neq' => 37));

        $array = $this->_getNode(Mage::getSingleton('catalog/category')->getTreeModel()->load()->getNodeById($rootCatId));

        $array = isset($array['children']) ? $array['children'] : $array;

        return $array;
    }

    private function _getNode($node, $level = 0)
    {
        $data = $this->categories->getItemById($node->getId());
        if (!$data) {
            return null;
        }
        if ($data || $level === 0) {
            $children = array();
            if ($node->hasChildren()) {
                foreach ($node->getChildren() as $child) {
                    $children[] = $this->_getNode($child, $level + 1);
                }
                $children = array_filter($children);
            }
            $data->setChildren($children);
        }

        return $data;
    }

    public function getLoginClass()
    {
        return !Mage::getSingleton('customer/session')->isLoggedIn() ? 'necessary-login' : '';
    }

    public function getVigLink(Mage_Catalog_Model_Product $product)
    {
        $_product = clone $product;
        $_product = $_product->load($_product->getId());
        if ($_product->getIsVerified() == false && $_product->getProductOriginUrl() == true) {
            return ' target="_blank" ';
        }

        return '';
    }

    /**
     * @param int $page
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getBestsellers($page = 1, $size = 18)
    {
        $bestSellerIds = Mage::getResourceModel('sales/report_bestsellers_collection')->getColumnValues('product_id');

        $collection = Mage::getResourceModel('catalog/product_collection')
                          ->addAttributeToFilter('entity_id', array('in' => $bestSellerIds))
                          ->addAttributeToSelect('*')
                          ->addAttributeToFilter('status', 1)
                          ->addAttributeToFilter('visibility', array('in' => array(2, 4)))
                          ->addAttributeToFilter('price', array('gt' => 0, 'lt' => 100))
                          ->setPageSize($size)
                          ->setCurPage($page);
        Mage::getResourceSingleton('cataloginventory/stock')->setInStockFilterToCollection($collection);

        $sortedIds = implode(',', $bestSellerIds);
        $collection->getSelect()->order(new Zend_Db_Expr("FIELD(`e`.`entity_id`, {$sortedIds})"));

        return $collection;
    }

    /**
     * @param int $page
     *
     * @return Mage_Catalog_Model_Resource_Product_Collection
     */
    public function getNewArrivals($page = 1, $size = 18)
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
                          ->addAttributeToSort('entity_id', Varien_Data_Collection::SORT_ORDER_DESC)
                          ->addAttributeToSelect('*')
                          ->addAttributeToFilter('status', 1)
                          ->addAttributeToFilter('visibility', array('in' => array(2, 4)))
                          ->addAttributeToFilter('price', array('gt' => 0, 'lt' => 100))
                          ->addAttributeToFilter('created_at',
                                                 array('from' => '-30 days', 'to' => true, 'date' => true))
                          ->setPageSize($size)
                          ->setCurPage($page);
        Mage::getResourceSingleton('cataloginventory/stock')->setInStockFilterToCollection($collection);

        return $collection;
    }

    /**
     *
     * @param $customerGroupId
     * @param $query
     *
     * @throws Mage_Core_Exception
     * @return Mage_Customer_Model_Resource_Customer_Collection
     */
    public function searchCustomers($customerGroupId, $query)
    {
        $attributes = array('username', 'firstname', 'lastname');
        $search = array();
        foreach ($attributes as $_attr) {
            $search[] = array('attribute' => $_attr, 'like' => "%{$query}%");
        }
        $collection = Mage::getResourceModel('customer/customer_collection')
                          ->addAttributeToSelect('*')
                          ->addAttributeToFilter('group_id', $customerGroupId)
                          ->addAttributeToFilter($search, null, 'left')
                          ->addAttributeToFilter('entity_id',
                                                 array('neq' => Mage::getSingleton('customer/session')->getCustomerId()));

        return $collection;
    }

    /**
     * @param $collection Mage_Catalog_Model_Resource_Product_Collection
     *
     * @return void
     */
    public function filterDeals($collection)
    {
        $dealsCatId = 37;
        $category = Mage::getModel('catalog/category')->load($dealsCatId);
        $catIds = Mage::getResourceSingleton('catalog/category')->getChildren($category);
        try {
            $collection->joinField('category_id',
                                   'catalog/category_product',
                                   'category_id',
                                   'product_id=entity_id',
                                   null,
                                   'left');
        } catch (Exception $e) {
        }
        $collection->addAttributeToFilter('category_id', array('in' => $catIds));
    }
}