<?php
class Beagles_Rewards_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    public function level($customerId){
        $clicksCount = count(Mage::getModel('rewards/clicks')->getCollection()->addFieldToFilter('uid', $customerId));
        $products = Mage::getModel('catalog/product')->getCollection()->addFieldToFilter('product_owner_id', $customerId);
        $productsCount = count($products);
        $productLikesCount = 0;
        $productCommentsCount = 0;
        foreach ($products as $product) {
            $productId = $product->getId();
            $productLikesCount += Mage::helper('GaussDev_Like')->countLikes($productId);
            $productCommentsCount += Mage::helper('gaussdev_comments')->commentCount($productId);
        }
        $levels = array(0.0, 10.0, 50.0);
        $score = (0.40 * $productLikesCount) + (0.40 * $productCommentsCount) + (0.20 * $productsCount);
        if ($score <= $levels[1]) {
            $level = 'one';
        } elseif ($score > $levels[1] && $score <= $levels[2]) {
            $level = 'two';
        } elseif ($score > $levels[2]) {
            $level = 'three';
        }
        return array('level'=>$level,
                     'score'=>$score,
                     'clicks'=>$clicksCount,
                     'likes'=>$productLikesCount,
                     'comments'=>$productCommentsCount,
                     'viglink_earnings'=>0.0,
                     'products'=>$productsCount);
    }
}