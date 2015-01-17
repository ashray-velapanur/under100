<?php

class GaussDev_SocialLogin_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_FACEBOOK_ID = 'sociallogin/facebook/id';
    const XML_PATH_FACEBOOK_SECRET = 'sociallogin/facebook/secret';
    const XML_PATH_GOOGLE_ID = 'sociallogin/google/id';
    const XML_PATH_GOOGLE_SECRET = 'sociallogin/google/secret';
    const XML_PATH_TWITTER_KEY = 'sociallogin/twitter/key';
    const XML_PATH_TWITTER_SECRET = 'sociallogin/twitter/secret';
    const XML_PATH_INSTAGRAM_ID = 'sociallogin/instagram/id';
    const XML_PATH_INSTAGRAM_SECRET = 'sociallogin/instagram/secret';

    public function opauthConfig()
    {
        return array(
            'path'               => "/sociallogin/oauth/auth/",
            'debug'              => true,
            'callback_url'       => "/sociallogin/oauth/callback",
            'security_salt'      => 'edaf2645017b11be488603c6a61f381ed248d7f5b36a8f8b423fe9aad92521f0',
            'security_iteration' => 500,
            'security_timeout'   => '10 seconds',
            'Strategy'           => array(
                'Facebook'  => array(
                    'app_id'     => Mage::getStoreConfig(self::XML_PATH_FACEBOOK_ID),
                    'app_secret' => Mage::getStoreConfig(self::XML_PATH_FACEBOOK_SECRET),
                    'scope'      => 'public_profile,email'
                ),
                'Google'    => array(
                    'client_id'     => Mage::getStoreConfig(self::XML_PATH_GOOGLE_ID),
                    'client_secret' => Mage::getStoreConfig(self::XML_PATH_GOOGLE_SECRET)
                ),
                'Twitter'   => array(
                    'key'    => Mage::getStoreConfig(self::XML_PATH_TWITTER_KEY),
                    'secret' => Mage::getStoreConfig(self::XML_PATH_TWITTER_SECRET)
                ),
                'Instagram' => array(
                    'client_id'     => Mage::getStoreConfig(self::XML_PATH_INSTAGRAM_ID),
                    'client_secret' => Mage::getStoreConfig(self::XML_PATH_INSTAGRAM_SECRET),
                )
            ),
        );
    }
}