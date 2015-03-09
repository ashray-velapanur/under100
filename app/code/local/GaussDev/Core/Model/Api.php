<?php

class GaussDev_Core_Model_Api extends Mage_Api_Model_Resource_Abstract
{


    public function bestsellers($arg){
    	if(!isset($arg['uid'])) return (array("error"=>"400")); //Malformed request.
    	if(empty($arg['uid'])) return (array("error"=>"402"));	//Empty uid
    	$customerId=$arg['uid'];
    	 
    	$page=1;
        if(isset($arg['page'])) (int) $page= $arg['page'];
        $collection= Mage::helper('gaussdev')->getBestsellers($page,20);
        $maxPages=$collection->getLastPageNumber();
        if($page > $maxPages) return  array("response"=>array());
        $result=array();
    	foreach ($collection as $product) {
            /** @var Mage_Catalog_Model_Product $_product */
            $_product = Mage::getModel('catalog/product')->load($product->getId());
            $productId = $_product->getId();
            $isLiked = Mage::helper('GaussDev_Like')->checkLiked($productId, $customerId);
            $likesCount = Mage::helper('GaussDev_Like')->countLikes($productId);
            $isCommented = Mage::helper('gaussdev_comments')->isCommented($productId, $customerId);
            $commentsCount = Mage::helper('gaussdev_comments')->commentCount($productId);
            $ownerId = $_product->getData('product_owner_id');
            $owner = Mage::helper('gaussdev_parser')->getOwner($ownerId);
            $ownerImage = Mage::helper('gaussdev_customerimages')->getUrl($ownerId);
            $price = number_format($_product->getFinalPrice(), 2, '.', '');
            $gallery = array();
            foreach ($_product->getMediaGalleryImages() as $image) {
                $gallery[] = $image->getUrl();
            }
            $result[] = array(
                'product_id'         => $productId,
                'sku'                => $_product->getSku(),
                'name'               => $_product->getName(),
                'set'                => $_product->getAttributeSetId(),
                'type'               => $_product->getTypeId(),
                'category_ids'       => $_product->getCategoryIds(),
                'website_ids'        => $_product->getWebsiteIds(),
                'is_verified'        => (bool)$_product->getIsVerified(),
                'is_liked'           => (bool)$isLiked,
                'likes_count'        => (int)$likesCount,
                'is_commented'       => (bool)$isCommented,
                'comments_count'     => (int)$commentsCount,
                'owner'              => $owner,
                'owner_image'        => $ownerImage,
                'price'              => $price,
                'description'        => $_product->getDescription(),
                'short_description'  => $_product->getShortDescription(),
                'image'              => $_product->getImage(),
                'small_image'        => $_product->getSmallImage(),
                'thumbnail'          => $_product->getThumbnail(),
                'product_owner_id'   => $ownerId,
                'product_origin_url' => $_product->getProductOriginUrl(),
                'gallery'            => $gallery,
                'brand'              => $_product->getBrand(),
            );
        }
        
        
        return array("response"=>$result);
    }
    
    
    
    
    public function newarrivals($arg){
    	if(!isset($arg['uid'])) return (array("error"=>"400")); //Malformed request.
    	if(empty($arg['uid'])) return (array("error"=>"402"));	//Empty uid
    	$customerId=$arg['uid'];
    	
    	
    	$page=1;
    	if(isset($arg['page'])) $page= (int) $arg['page'];
    	$collection= Mage::helper('gaussdev')->getNewArrivals($page,20);
    	$result=array();
  		$maxPages=$collection->getLastPageNumber();
    	//return $collection->getSize();
  		if($page > $maxPages) return  array("response"=>array());
    	
    	foreach ($collection as $product) {
    		/** @var Mage_Catalog_Model_Product $_product */
    		$_product = Mage::getModel('catalog/product')->load($product->getId());
    		$productId = $_product->getId();
    		$isLiked = Mage::helper('GaussDev_Like')->checkLiked($productId, $customerId);
    		$likesCount = Mage::helper('GaussDev_Like')->countLikes($productId);
    		$isCommented = Mage::helper('gaussdev_comments')->isCommented($productId, $customerId);
    		$commentsCount = Mage::helper('gaussdev_comments')->commentCount($productId);
    		$ownerId = $_product->getData('product_owner_id');
    		$owner = Mage::helper('gaussdev_parser')->getOwner($ownerId);
    		$ownerImage = Mage::helper('gaussdev_customerimages')->getUrl($ownerId);
    		$price = number_format($_product->getFinalPrice(), 2, '.', '');
    		$gallery = array();
    		foreach ($_product->getMediaGalleryImages() as $image) {
    			$gallery[] = $image->getUrl();
    		}
    		$result[] = array(
    				'product_id'         => $productId,
    				'sku'                => $_product->getSku(),
    				'name'               => $_product->getName(),
    				'set'                => $_product->getAttributeSetId(),
    				'type'               => $_product->getTypeId(),
    				'category_ids'       => $_product->getCategoryIds(),
    				'website_ids'        => $_product->getWebsiteIds(),
    				'is_verified'        => (bool)$_product->getIsVerified(),
    				'is_liked'           => (bool)$isLiked,
    				'likes_count'        => (int)$likesCount,
    				'is_commented'       => (bool)$isCommented,
    				'comments_count'     => (int)$commentsCount,
    				'owner'              => $owner,
    				'owner_image'        => $ownerImage,
    				'price'              => $price,
    				'description'        => $_product->getDescription(),
    				'short_description'  => $_product->getShortDescription(),
    				'image'              => $_product->getImage(),
    				'small_image'        => $_product->getSmallImage(),
    				'thumbnail'          => $_product->getThumbnail(),
    				'product_owner_id'   => $ownerId,
    				'product_origin_url' => $_product->getProductOriginUrl(),
    				'gallery'            => $gallery,
    				'brand'              => $_product->getBrand(),
    		);
    	}
    
    	return array("response"=>$result);
    }

    protected $_filtersMap = array(
        'product_id' => 'entity_id',
        'set'        => 'attribute_set_id',
        'type'       => 'type_id'
    );

    /**
     * @param     $customerId
     * @param     $catIds
     * @param int $currentPage
     * @param int $pageSize
     *
     * @return array
     * @throws Mage_Core_Exception
     */
    public function homefeed(
        $sortBy, $order, $customerId, $catIds = array(), $currentPage = 1, $pageSize = 20, $filters = array(), $sortByIds = array()
    ) {
        $sortByIds = (array)$sortByIds;
        $currentPage = ($currentPage > 1) ? (int)$currentPage : 1;
        $pageSize = ($pageSize > 1) ? (int)$pageSize : 20;
        /** @var Mage_Catalog_Model_Resource_Product_Collection $collection */
        $collection = Mage::getResourceModel('catalog/product_collection')
                          ->setStore('default')
                          ->addAttributeToFilter('status', 1)
                          ->addAttributeToFilter('visibility', 4)
                          ->addAttributeToFilter('price', array('gt' => 0, 'lt' => 100))
                          ->setPage($currentPage, $pageSize);
        if ($sortBy && $order) {
            $collection->setOrder($sortBy, $order);
        }

        if (!$sortByIds) {
            $collection->setOrder('popularityscore', 'DESC');
        }

        $filters = Mage::helper('api')->parseFilters($filters, $this->_filtersMap);
        try {
            foreach ($filters as $_attribute => $_condition) {
                $collection->addFieldToFilter($_attribute, $_condition);
            }
        } catch (Mage_Core_Exception $e) {
            $this->_fault('filters_invalid', $e->getMessage());
        }
        if ($catIds) {
            $collection->joinField('category_id', 'catalog/category_product', 'category_id', 'product_id=entity_id',
                null, 'left')->addAttributeToFilter('category_id', array('in' => $catIds));
        }

        Mage::getResourceSingleton('cataloginventory/stock')->setInStockFilterToCollection($collection);
        $result = array();
        if ($collection->getCurPage() != $currentPage) {
            return $result;
        }
        if ($sortByIds) {
            $idsCol = clone $collection;
            $ids = $idsCol->load()->getLoadedIds();
            foreach ($ids as $_id) {
                if (!in_array($_id, $sortByIds)) {
                    $sortByIds[] = $_id;
                }
            }

            $order = new Zend_Db_Expr('FIELD(`e`.`entity_id`, ' . implode(',', $sortByIds) . ')');
            $collection->getSelect()->order($order);
        }
        foreach ($collection as $product) {
            /** @var Mage_Catalog_Model_Product $_product */
            $_product = Mage::getModel('catalog/product')->load($product->getId());
            $result[] = Mage::helper('gaussdev')->getProductDetails($_product, $customerId);
        }
        return $result;
    }
}