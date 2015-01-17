<?php

class GaussDev_SocialLogin_MobileController extends Mage_Core_Controller_Front_Action
{
    public function googleAction()
    {
        $this->_redirect('*/oauth/auth', array('google' => true, 'mobile' => true));
    }

    public function facebookAction()
    {
        $this->_redirect('*/oauth/auth', array('facebook' => true, 'mobile' => true));
    }

    public function instagramAction()
    {
        $this->_redirect('*/oauth/auth', array('instagram' => true, 'mobile' => true));
    }

    public function twitterAction()
    {
        $this->_redirect('*/oauth/auth', array('twitter' => true, 'mobile' => true));
    }
}