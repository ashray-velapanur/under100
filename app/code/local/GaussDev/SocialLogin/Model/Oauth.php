<?php

class GaussDev_SocialLogin_Model_Oauth extends Mage_Core_Model_Abstract
{
    /**
     * @param $response
     *
     * @throws Exception
     * @return Mage_Customer_Model_Customer
     */
    public function processCustomer($response)
    {
        $provider = $response['auth']['provider'];
        $uid = $response['auth']['uid'];
        $oauth = Mage::getModel('gaussdev_sociallogin/oauth')->loadByProviderAndUid($provider, $uid);
        if (!$oauth->getId()) {
            $info = $response['auth']['info'];
            $image = isset($info['image']) ? $info['image'] : null;
            $email = isset($info['email']) ? $info['email'] : null;
            if (isset($info['first_name'], $info['last_name'])) {
                $firstName = $info['first_name'];
                $lastName = $info['last_name'];
            } else {
                $name = preg_split("/(?!['-])\P{L}+/u", $info['name'], 2, PREG_SPLIT_NO_EMPTY);
                if (count($name) === 2) {
                    $firstName = $name[0];
                    $lastName = $name[1];
                } else {
                    $firstName = $info['name'];
                    $lastName = '';
                }
            }

            $customer = Mage::getModel('customer/customer')
                            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                            ->loadByEmail($email);
            if (!$customer->getId()) {
                unset($customer);
                $customer = Mage::getModel('customer/customer')
                                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                                ->setFirstname($firstName)
                                ->setLastname($lastName)
                                ->setEmail($email)
                                ->setPassword(Mage::getModel('customer/customer')->generatePassword(40))
                                ->setHasRandomPassword(1)
                                ->setTelephone(isset($info['phone']) ? $info['phone'] : null)
                                ->setIsActive(1)
                                ->setSocialLogin(true)
                                ->save();
            }
            if ($customer->getId()) {
                if ($image) {
                    $file_info = new finfo(FILEINFO_MIME_TYPE);
                    $ua = 'Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0';
                    $headers = array(
                        "User-Agent: {$ua}",
                        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                        'Accept-Charset: utf-8',
                        'Accept-Language: en-US,en;q=0.5',
                    );
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $image);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_USERAGENT, $ua);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_COOKIEFILE, '');
                    curl_setopt($ch, CURLOPT_ENCODING, '');
                    $imageContents = curl_exec($ch);
                    curl_close($ch);
                    $mime_type = $file_info->buffer($imageContents);
                    $ext = pathinfo($image, PATHINFO_EXTENSION);
                    Mage::helper('gaussdev_customerimages')->changeImage($customer, $imageContents, $mime_type, $ext);
                }
                $oauth->setProvider($provider)->setUid($uid)->setCustomerId($customer->getId())->save();
            } else {
                throw new Exception ('Error while registering customer.');
            }
        }

        return Mage::getModel('customer/customer')->load($oauth->getCustomerId());
    }

    public function loadByProviderAndUid($provider, $uid)
    {
        $item = $this->getCollection()
                     ->addFieldToFilter('provider', $provider)
                     ->addFieldToFilter('uid', $uid)
                     ->fetchItem();
        $oauth = clone $this;
        if ($item) {
            $oauth->load($item->getId());
        }
        if (Mage::getModel('customer/customer')->load($oauth->getCustomerId())->getId()) {
            return $oauth;
        } else {
            $oauth->delete();

            return $this;
        }
    }

    protected function _construct()
    {
        $this->_init('gaussdev_sociallogin/oauth');
    }
}