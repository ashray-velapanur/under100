<?php

class GaussDev_Comments_CommentController extends Mage_Core_Controller_Front_Action
{

    public function newAction()
    {
        try {
            if (!$this->_validateFormKey()) {
                throw new Exception('Invalid Form Key.');
            }
            $customerId = Mage::getSingleton('customer/session')->getCustomerId();
            $productId = $this->getRequest()->getPost('product_id');
            $parentId = $this->getRequest()->getPost('parent_id');
            $message = $this->getRequest()->getPost('message');
            $tags = $this->getRequest()->getPost('tags');
            $tags = $tags ? json_decode($tags) : array();
            if ($message && $customerId && $productId) {
                foreach ((array)$tags as $tag) {
                    if (!isset($tag->position, $tag->customer_id, $tag->content)) {
                        throw new Exception('Invalid Tag Params: ' . print_r($tags, true));
                    }
                    if (substr_compare($message, $tag->content, $tag->position, strlen($tag->content)) === 0) {
                        $message = substr_replace($message, '', $tag->position, strlen($tag->content));
                    } else {
                        throw new Exception('Invalid Params: ' . print_r($this->getRequest()->getPost(), true));
                    }
                }
                /** @var GaussDev_Comments_Model_Comment $comment */
                $comment = Mage::getModel('gaussdev_comments/comment')
                               ->setCustomerId($customerId)
                               ->setProductId($productId)
                               ->setParentId($parentId)
                               ->setMessage($message)
                               ->save();
                try {
                    foreach ((array)$tags as $tag) {
                        $dataArray = array(
                            'position'    => (string)$tag->position,
                            'customer_id' => (string)$tag->customer_id,
                            'comment_id'  => (string)$comment->getId()
                        );
                        if (count(array_filter($dataArray, 'ctype_digit')) !== 3) {
                            throw new Exception('Invalid Tag Params: ' . print_r($dataArray, true));
                        }
                        Mage::getModel('gaussdev_comments/tag')->setData($dataArray)->save();
                    }
                } catch (Exception $e) {
                    $comment->delete();
                    throw $e;
                }
            }
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
        }
        $this->_redirectReferer();
    }

    public function likeAction()
    {
        /** @var GaussDev_Comments_Model_Comment $comment */
        $response = false;
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $commentId = $this->getRequest()->getPost('comment_id');
        try {
            $comment = Mage::getModel('gaussdev_comments/comment')->load($commentId);
            $like = Mage::getModel('gaussdev_comments/like')->loadByFilter($customerId, $commentId);
            if ($comment->hasData() && !$like->hasData() && $customerId) {
                $comment->incrementLikes()->save();
                $like->setCustomerId($customerId)->setCommentId($commentId)->save();
                $response = true;
            }
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
        }
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($response));
    }

    public function unlikeAction()
    {
        /** @var GaussDev_Comments_Model_Comment $comment */
        $response = false;
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $commentId = $this->getRequest()->getPost('comment_id');
        try {
            $comment = Mage::getModel('gaussdev_comments/comment')->load($commentId);
            $like = Mage::getModel('gaussdev_comments/like')->loadByFilter($customerId, $commentId);
            if ($comment->hasData() && $like->hasData() && $customerId) {
                $comment->decrementLikes()->save();
                $like->delete();
                $response = true;
            }
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
        }
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($response));

    }

    public function reportAction()
    {
        /** @var GaussDev_Comments_Model_Comment $comment */
        $response = false;
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $commentId = $this->getRequest()->getPost('comment_id');
        try {
            $comment = Mage::getModel('gaussdev_comments/comment')->load($commentId);
            $report = Mage::getModel('gaussdev_comments/report')->loadByFilter($customerId, $commentId);
            if ($comment->hasData() && !$report->hasData()) {
                $comment->incrementReports()->save();
                $report->setCustomerId($customerId)->setCommentId($commentId)->save();
                $response = true;
            }
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
        }
        $this->getResponse()->clearHeaders()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setBody(json_encode($response));
    }

    public function getAction()
    {
        $this->loadLayout();
        $myBlock = $this->getLayout()->createBlock('gaussdev_comments/product_comments');
        $myBlock->setTemplate('comments/comments.phtml');
        $myHtml = $myBlock->toHtml();
        $this->getResponse()->setHeader('Content-Type', 'text/html')->setBody($myHtml);
    }

}