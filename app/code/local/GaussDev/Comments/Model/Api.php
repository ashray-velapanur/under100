<?php

class GaussDev_Comments_Model_Api extends Mage_Api_Model_Resource_Abstract
{
    private function updateNewCommentTags($uid, $cid, $pid) {
        $timestamp = time();
        Mage::log(Mage::getModel('gaussdev_comments/newtag'));
        Mage::getModel('gaussdev_comments/newtag')
            ->setTimestamp($timestamp)
            ->setUid($uid)
            ->setCid($cid)
            ->setPid($pid)
            ->setViewed(false)
            ->save();
    }

    public function newTags($uid){
        $collection = Mage::getModel('gaussdev_comments/newtag')
                  ->getCollection()
                  ->addFieldToFilter('uid', $uid)
                  ->toArray();
        $response = array();
        foreach ($collection['items'] as $tag) {
            $cid = $tag['cid'];
            $pid = $tag['pid'];
            $timestamp = $tag['timestamp'];
            $viewed = $tag['viewed'];
            $name = Mage::getModel('customer/customer')->load($cid)->getName();
            $productName = Mage::getModel('catalog/product')->load($pid)->getName();
            $response[] = array('timestamp'=>$timestamp, 'name'=>$name, 'uid'=>$cid, 'productId'=>$pid, 'productName'=>$productName, 'viewed'=>$viewed);
        }
        return $response;
    }

    public function clearNewTags($uid) {
        $collection = Mage::getModel('gaussdev_comments/newtag')
                  ->getCollection()
                  ->addFieldToFilter('uid', $uid);
        foreach ($collection as $key => $tag) {
            $tag->setViewed(true)->save();
        }
    }

    public function create($customerId, $productId, $message, $taggedId = null, $parentId = null)
    {
        if (!is_numeric($customerId) || !is_numeric($productId) || empty($message)
            || !$this->customerExists($customerId)
            || !$this->productExists($productId)
        ) {
            $this->_fault('data_invalid');
        }
        if (isset($parentId)) {
            if (!is_numeric($parentId) || !$this->commentExists($parentId)) {
                $this->_fault('data_invalid');
            }
        }

        try {
            Mage::getModel('gaussdev_comments/comment')
                ->setCustomerId($customerId)
                ->setProductId($productId)
                ->setParentId($parentId)
                ->setMessage($message)
                ->save();
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
            $this->_fault('data_invalid', $e->getMessage());
        }

        if ($taggedId AND $taggedId != $customerId) {
            $this->updateNewCommentTags($taggedId, $customerId, $productId);
            $name = Mage::getModel('customer/customer')->load($taggedId)->getName();
            $email = Mage::getModel('customer/customer')->load($taggedId)->getEmail();
            $friendName = Mage::getModel('customer/customer')->load($customerId)->getName();
            $productName = Mage::getModel('catalog/product')->load($productId)->getName();
            $templateValues = array(
                array(
                    'name' => 'name',
                    'content' => $name
                ),
                array(
                    'name' => 'friend_name',
                    'content' => $friendName
                ),
                array(
                    'name' => 'product_name',
                    'content' => $productName
                ),
                array(
                    'name' => 'message',
                    'content' => $message
                )
            );
            $subject = "You're getting popular";
            $this->sendEmailUsingMandrill('UserTaggedMessage', $email, $name, $subject, $templateValues);
        }

        return true;
    }

    protected function sendEmailUsingMandrill($template_name, $email, $name, $subject, $templateValues)
    {
        $template_content = array(
            array()
        );
        $message = array(
            'subject' => $subject,
            'from_email' => 'cole@under100.co',
            'from_name' => 'Cole Glass',
            'to' => array(
                array(
                    'email' => $email,
                    'name' => $name,
                    'type' => 'to'
                )
            ),
            'important' => true,
            'track_opens' => false,
            'track_clicks' => false,
            'auto_text' => false,
            'auto_html' => null,
            'inline_css' => null,
            'url_strip_qs' => null,
            'preserve_recipients' => null,
            'view_content_link' => null,
            'tracking_domain' => null,
            'signing_domain' => null,
            'return_path_domain' => null,
            'merge' => true,
            'merge_language' => 'mailchimp',
            'global_merge_vars' => $templateValues,
            'google_analytics_domains' => array('theunder100.com'),
            'metadata' => array('website' => 'www.theunder100.com')
        );
        $async = false;
        $ip_pool = 'Main Pool';
        $send_at = null;
        $mandrill = new Mandrill('VPQpbpMCNPIYEH9aVev2GQ');
        $result = $mandrill->messages->sendTemplate($template_name, $template_content, $message, $async, $ip_pool, $send_at);
}

    protected function customerExists($id)
    {
        return Mage::getModel('customer/customer')->load($id)->hasData();
    }

    protected function productExists($id)
    {
        return Mage::getModel('catalog/product')->load($id)->hasData();
    }

    protected function commentExists($id)
    {
        return Mage::getModel('gaussdev_comments/comment')->load($id)->hasData();
    }

    public function like($customerId, $commentId)
    {
        /** @var GaussDev_Comments_Model_Comment $comment */
        if (!is_numeric($customerId) || !is_numeric($commentId) || !$this->customerExists($customerId)) {
            $this->_fault('data_invalid');
        }
        try {
            $comment = Mage::getModel('gaussdev_comments/comment')->load($commentId);
            $like = Mage::getModel('gaussdev_comments/like')->loadByFilter($customerId, $commentId);
            if ($comment->hasData() && !$like->hasData()) {
                $comment->incrementLikes()->save();
                $like->setCustomerId($customerId)->setCommentId($commentId)->save();

            }
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
            $this->_fault('data_invalid', $e->getMessage());
        }

        return true;
    }

    public function unlike($customerId, $commentId)
    {
        /** @var GaussDev_Comments_Model_Comment $comment */
        if (!is_numeric($customerId) || !is_numeric($commentId) || !$this->customerExists($customerId)) {
            $this->_fault('data_invalid');
        }
        try {
            $comment = Mage::getModel('gaussdev_comments/comment')->load($commentId);
            $like = Mage::getModel('gaussdev_comments/like')->loadByFilter($customerId, $commentId);
            if ($comment->hasData() && $like->hasData()) {
                $comment->decrementLikes()->save();
                $like->delete();
            }
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
            $this->_fault('data_invalid', $e->getMessage());
        }

        return true;
    }

    public function report($customerId, $commentId)
    {
        /** @var GaussDev_Comments_Model_Comment $comment */
        if (!is_numeric($customerId) || !is_numeric($commentId) || !$this->customerExists($customerId)) {
            $this->_fault('data_invalid');
        }
        try {
            $comment = Mage::getModel('gaussdev_comments/comment')->load($commentId);
            $report = Mage::getModel('gaussdev_comments/report')->loadByFilter($customerId, $commentId);
            if ($comment->hasData() && !$report->hasData()) {
                $comment->incrementReports()->save();
                $report->setCustomerId($customerId)->setCommentId($commentId)->save();

            }
        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
            $this->_fault('data_invalid', $e->getMessage());
        }

        return true;
    }

    public function getAll($productId, $customerId, $sort = null, $curPage = null, $pageSize = null)
    {
        if (!is_numeric($productId) || !is_numeric($customerId) || !$this->customerExists($customerId)) {
            $this->_fault('data_invalid');
        }
        $comments = array();
        try {
            $comments = Mage::getModel('gaussdev_comments/comment')
                            ->getSortedCommentsData($productId, $customerId, $sort, $curPage, $pageSize);

        } catch (Exception $e) {
            Mage::helper('gaussdev')->log($e);
            $this->_fault('data_invalid', $e->getMessage());
        }

        return $comments->toArray();
    }

}