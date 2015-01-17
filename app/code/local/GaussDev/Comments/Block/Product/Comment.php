<?php

class GaussDev_Comments_Block_Product_Comment extends Mage_Core_Block_Template
{
    public $comment;

    public function addTags($comment, $tags)
    {
        $inserts = array();
        foreach ($tags as $tag) {
            $position = (int)$tag->getPosition();
            $customer = $tag->getCustomer();
            if (!$customer) {
                $deleted = $this->__('deleted');
                $name = "<span style=\"background: #d3d3d3;text-decoration: line-through;\">{$deleted}</span>";
            } else {
                $url = Mage::getUrl(null, array('_direct' => 'profile', '_query' => array('id' => $customer->getId())));
                $name = '@';
                $name .= $customer->getUsername() ?: ($customer->getFirstname() . ' ' . $customer->getLastname());
                $name = "<span style=\"background: #add8e6;text-decoration: underline\"><a href=\"{$url}\">{$name}</a></span>";
            }
            $inserts[$position] = $name;
        }
        arsort($inserts, SORT_NUMERIC);
        foreach ($inserts as $pos => $insert) {
            $comment = substr_replace($comment, $insert, $pos, 0);
        }

        return $comment;
    }
}