<?php

/**
 * Stripe payment model
 *
 * @category    Radweb
 * @package     Radweb_Stripe
 * @author      Artur Salagean <info@radweb.co.uk>
 * @copyright   Radweb (http://radweb.co.uk)
 *
 */

require_once Mage::getBaseDir('lib') . DS . 'Stripe' . DS . 'Stripe.php';

class Radweb_Stripe_Model_Payment extends Mage_Payment_Model_Method_Cc
{
    protected $_code = 'radweb_stripe';
    protected $_formBlockType = 'radweb_stripe/form';
    protected $_infoBlockType = 'radweb_stripe/info';

    const XML_PATH_SEND_TO_ADMIN_EMAIL = "payment/radweb_stripe/email_template_sent_to_admin";
    const XML_PATH_EMAIL_IDENTITY = "payment/radweb_stripe/email_sender";
    const XML_PATH_GENERAL_EMAIL_IDENTITY = "trans_email/ident_general";
    const XML_PATH_ADMIN_EMAIL_IDENTITY = "payment/radweb_stripe/email_receive_contact_form";
    const XML_PATH_PAYMENT_ACTION = "payment/radweb_stripe/payment_type";


    protected $_isGateway = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    //protected $_canSaveCc = false;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = true;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = true;
    protected $_canSaveCc = false;
    protected $_canFetchTransactionInfo = true;
    protected $_canReviewPayment = true;

    //protected $_canFetchTransactionInfo = true;
    //protected $_canReviewPayment        = true;

    //protected $_supportedCurrencyCodes = array('USD','GBP', 'EUR', 'AUD', 'CAD');
    protected $_minOrderTotal = 0.5;

    protected $_storeId;

    public function __construct()
    {
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {
            Stripe::setApiKey($this->getConfigData('api_key',
                Mage::getSingleton('adminhtml/session_quote')->getStore()->getCode()));
        } else {
            Stripe::setApiKey($this->getConfigData('api_key'));
        }
    }


    /**
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param float                          $amount
     *
     * @return $this
     * @throws Exception
     * @throws Mage_Core_Exception
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();
        $billing = $order->getBillingAddress();

        $this->_storeId = $order->getStoreId();

        $storeName = $order->getStore()->getFrontendName();

        $payment_action = Mage::getStoreConfig(self::XML_PATH_PAYMENT_ACTION, $this->_storeId);

        $capture = $payment_action == '1' ? true : false;

        $transId = $payment->getcc_trans_id();


        //echo 'asd';
        //var_dump($capture);
        //die();


        //echo 'asd';
        //echo $_POST['stripeToken'];

        if (isset($_POST['stripeToken'])) {
            $token = $_POST['stripeToken'];
        } else {
            $token = '';
        }

        if (!$token) {
            $token = $payment->getAdditionalInformation('stripe_token') ?: '';
        }
        if ($token) {
            Stripe_Token::retrieve($token);
        }

        $customer_id = $order->getCustomerId();
        $customer_name = $order->getCustomerName();
        $customer_email = $order->getCustomerEmail();

        //echo 'Customer Id:'.var_dump($customer_id);
        if (isset($_POST['stripe_card'])) {
            $stripe_card = $_POST['stripe_card'];
        } else {
            $stripe_card = '-1';
        }
        if ($stripe_card == null) {
            $stripe_card = '-1';
        }
        if ($stripe_card === '-1') {
            $stripe_card = $payment->getAdditionalInformation('stripe_card') ?: '-1';
        }


        if ($customer_id != null) {

            $model = Mage::getModel('radweb_stripe/users');

            $stripe_user = $model->loadById($customer_id);

            $customer_token = $stripe_user->getCustomerToken();


            if ($customer_token == null) {

                if (isset($_POST['save_stripe_card'])) {
                    $save_stripe_card = $_POST['save_stripe_card'] ? 'true' : 'false';
                } else {
                    $save_stripe_card = 'false';
                }

                if ($save_stripe_card == null) {
                    $save_stripe_card = 'false';
                }

                if ($save_stripe_card === 'false') {
                    $save_stripe_card = $payment->getAdditionalInformation('save_stripe_card') ? 'true' : 'false';
                }

                $model->setUserId($customer_id);
                $model->setStripeToken($token);

                $stripeCustomer = Stripe_Customer::create(array(
                    "description" => $customer_name,
                    "email"       => $customer_email,
                    //"card" => $token // obtained with Stripe.js
                ));

                $model->setCustomerToken($stripeCustomer->id);
                $customer_token = $stripeCustomer->id;

                $model->save();
            } else {

                $stripeCustomer = Stripe_Customer::retrieve($customer_token);

                if ($stripe_card != '-1') {
                    //echo $stripe_card;
                    //die();
                    $cards = $stripeCustomer->cards->data;
                    $card = $cards[$stripe_card];
                } else {
                    if (isset($_POST['save_stripe_card'])) {
                        $save_stripe_card = $_POST['save_stripe_card'] ? 'true' : 'false';
                    } else {
                        $save_stripe_card = 'false';
                    }

                    if ($save_stripe_card == null) {
                        $save_stripe_card = 'false';
                    }

                    if ($save_stripe_card === 'false') {
                        $save_stripe_card = $payment->getAdditionalInformation('save_stripe_card') ? 'true' : 'false';
                    }

                }

            }
        } else {
            $save_stripe_card = 'false';
        }

        //var_dump(strlen($token));
        //die();
        $finalAmount = $this->checkCurrency($order->getBaseCurrencyCode(), $amount);
        //var_dump($finalAmount);
        //die();

        try {


            if ($stripe_card == '-1' && $save_stripe_card == 'true') {

                $card = $stripeCustomer->cards->create(array("card" => $token));

                $charge = Stripe_Charge::create(array(
                    'amount'      => $finalAmount,
                    'currency'    => strtolower($order->getBaseCurrencyCode()),
                    'card'        => $card->id,
                    'customer'    => $customer_token,
                    'description' => sprintf('Payment for Order #%s on %s', $order->getIncrementId(), $storeName),
                    'capture'     => $capture

                ));
            } else {
                if ($stripe_card != '-1') {
                    $charge = Stripe_Charge::create(array(
                        'amount'      => $finalAmount,
                        'currency'    => strtolower($order->getBaseCurrencyCode()),
                        'card'        => $card->id,
                        'customer'    => $customer_token,
                        'description' => sprintf('Payment for Order #%s on %s', $order->getIncrementId(), $storeName),
                        'capture'     => $capture

                    ));
                } else {
                    if ($stripe_card == '-1' && ($save_stripe_card == 'false') && strlen($token) > 0) {

                        $charge = Stripe_Charge::create(array(
                            'amount'      => $finalAmount,
                            'currency'    => strtolower($order->getBaseCurrencyCode()),
                            'card'        => $token,
                            'description' => sprintf('Payment for Order #%s on %s', $order->getIncrementId(),
                                $storeName),
                            'capture'     => $capture
                        ));

                    } else {
                        if (!empty($transId)) {
                            //var_dump($transId);

                            try {
                                $charge = Stripe_Charge::retrieve($transId);
                                $charge->capture();
                                $capture = true;
                            } catch (Exception $e) {
                                Mage::throwException(Mage::helper('payment')
                                                         ->__("The payment accept failed with the error: "
                                                             . $e->getMessage()));

                            }

                            //die();
                        } else {
                            // echo 'kkt';
                        }
                    }
                }
            }


            $transactionId = $charge['id'];
            $transactionType = $charge['livemode'];

            $payment->setcc_trans_id($transactionId);

            //echo $transactionId;

            $transaction = Stripe_Charge::retrieve($transactionId);
            $card = $transaction->card;
            $checks = $card->__toJSON();
            $obj = json_decode($checks);

            //print_r($obj);

            $last = $obj->{'last4'};
            $type = $obj->{'brand'};
            $exp_month = $obj->{'exp_month'};
            $exp_year = $obj->{'exp_year'};
            $owner = $obj->{'name'};

            $payment->setcc_exp_month($exp_month);
            $payment->setcc_exp_year($exp_year);
            $payment->setcc_type($type);
            $payment->setcc_last4($last);
            $payment->setcc_owner($owner);

            //die();

            $addressCheck = $obj->{'address_line1_check'};

            if ($addressCheck == 'fail') {
                $payment->setaddress_status('failed');
            }

            $addressZipCheck = $obj->{'address_zip_check'};

            if ($addressZipCheck == 'fail') {
                $payment->setcc_status('failed');
            }

            $cvvCheck = $obj->{'cvc_check'};

            if ($cvvCheck == 'fail') {
                $payment->setcc_avs_status('failed');
            }

            if ($transactionType === false) {
                $payment->setcc_cid_status('testmode');
            }


        } catch (Exception $e) {
            //print_r($e);
            //die();
            //print_r($status);
            $this->debugData($e->getMessage());
            $error = $e->getMessage();

            $sendError = Mage::getStoreConfig('payment/radweb_stripe/email_failed', $this->_storeId);

            if ($sendError) {
                $this->sendError($order, $payment, $error);
            }

            Mage::throwException(Mage::helper('paygate')->__($error));
        }

        if ($capture) {

            $payment->setTransactionId($charge->id)->setIsTransactionClosed(0);

        } else {
            $payment->setTransactionId($charge->id)//->setIsTransactionClosed(0);
                    ->setIsTransactionPending(true);

            $message = Mage::helper('paypal')->__('Authorized amount of ');

            $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH, null, true, $message);

        }

        return $this;
    }

    private function checkCurrency($currency, $amount)
    {
        $currencies = array(
            'BIF',
            'CLP',
            'DJF',
            'GNF',
            'JPY',
            'KMF',
            'KRW',
            'MGA',
            'PYG',
            'RWF',
            'VUV',
            'XAF',
            'XOF',
            'XPF'
        );
        //var_dump($currency);
        if (in_array($currency, $currencies)) {
            return round($amount, 0);
        } else {
            return $amount * 100;
        }

        //die();
    }

    public function denyPayment(Mage_Payment_Model_Info $payment)
    {

        $storeId = $payment->getMethodInstance()->getStore();
        $api_key = $this->getConfigData('api_key', $storeId);
        Stripe::setApiKey($api_key);

        $transactionId = $payment->getcc_trans_id();

        try {
            $ch = Stripe_Charge::retrieve($transactionId);
            $ch->refund();
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('payment')->__("The payment deny failed with the error: "
                . $e->getMessage()));

        }

        return $this;
    }

    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {

        $storeId = $payment->getMethodInstance()->getStore();
        $api_key = $this->getConfigData('api_key', $storeId);
        Stripe::setApiKey($api_key);

        if (!$this->canReviewPayment($payment)) {
            Mage::throwException(Mage::helper('payment')->__('The payment review action is unavailable.'));
        }

        //echo 'kkt';
        $transactionId = $payment->getcc_trans_id();

        try {
            $ch = Stripe_Charge::retrieve($transactionId);
            $ch->capture();
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('payment')->__("The payment accept failed with the error: "
                . $e->getMessage()));

        }

        //die();

        return $this;
    }


    public function refund(Varien_Object $payment, $amount)
    {
        $storeId = $payment->getMethodInstance()->getStore();
        $api_key = $this->getConfigData('api_key', $storeId);
        Stripe::setApiKey($api_key);

        $transactionId = $payment->getData('cc_trans_id');

        $order = $payment->getOrder();
        $toRefund = $order->getBaseTotalRefunded()
            - $payment->getBaseAmountRefunded(); // previously just getBaseTotalRefunded()
        $refundAmount = Mage::app()->getStore()->roundPrice($toRefund);

        //echo 'Amount: '.$refundAmount;
        //echo 'asd';
        //die();

        try {
            Stripe_Charge::retrieve($transactionId)->refund(array('amount' => $refundAmount * 100));
        } catch (Exception $e) {
            $this->debugData($e->getMessage());
            Mage::throwException(Mage::helper('paygate')->__('Payment refunding error.'));
        }

        $payment->setTransactionId($transactionId . '-' . Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND)
                ->setParentTransactionId($transactionId)
                ->setIsTransactionClosed(1)
                ->setShouldCloseParentTransaction(1);

        return $this;
    }

    /*
	public function isAvailable($quote = null)
    {
    	if($quote && $quote->getBaseGrandTotal()<$this->_minOrderTotal) {
    		return false;
    	}

        return $this->getConfigData('api_key', ($quote ? $quote->getStoreId() : null))
            && parent::isAvailable($quote);
    }
    */

    public function canUseForCurrency($currencyCode)
    {
        return true;
    }

    public function getVerificationRegEx()
    {
        $verificationExpList = array(
            'VI'  => '/^[0-9]{3}$/', // Visa
            'MC'  => '/^[0-9]{3}$/',       // Master Card
            'AE'  => '/^[0-9]{4}$/',        // American Express
            'DI'  => '/^[0-9]{3}$/',          // Discovery
            'DIN' => '/^[0-9]{3}$/',          // Diners Club
            'SS'  => '/^[0-9]{3,4}$/',
            'SM'  => '/^[0-9]{3,4}$/', // Switch or Maestro
            'SO'  => '/^[0-9]{3,4}$/', // Solo
            'OT'  => '/^[0-9]{3,4}$/',
            'JCB' => '/^[0-9]{3,4}$/' //JCB
        );

        return $verificationExpList;
    }


    public function validate()
    {
        try {
            $info = $this->getInfoInstance();
            //echo 'asd';

            if (isset($_POST['stripeToken'])) {
                $token = $_POST['stripeToken'];
            } else {
                $token = '';
            }

            if (!$token) {
                $token = $info->getAdditionalInformation('stripe_token') ?: '';
            }

            //echo $token;
            //$payment = Mage::getSingleton('checkout/session')->getQuote()->getPayment();
            //print_r($payment);

            if (!empty($token)) {
                $transaction = Stripe_Token::retrieve($token);

                //print_r($card);

                //die();


                $card = $transaction->card;

                //print_r($card);

                $checks = $card->__toJSON();
                $obj = json_decode($checks);

                //$payment->setCcNumber('123');
                //$payment->setCcExpMonth('123');
                //$payment->setCcExpYear('123');

                //print_r($obj);

                $last = $obj->{'last4'};
                $type = $obj->{'brand'};
                $exp_month = $obj->{'exp_month'};
                $exp_year = $obj->{'exp_year'};
                $owner = $obj->{'name'};
            } elseif (isset($_POST['stripe_card']) || $info->getAdditionalInformation('stripe_card')) {
                $stripe_card = isset($_POST['stripe_card']) ? $_POST['stripe_card'] : null;
                $stripe_card = $stripe_card !== null ? $stripe_card : $info->getAdditionalInformation('stripe_card');
                $customer = Mage::getSingleton('customer/session')->getCustomer();
                $customer_id = $customer->getId();

                //var_dump($customerId);


                $model = Mage::getModel('radweb_stripe/users');

                $stripe_user = $model->loadById($customer_id);

                $customer_token = $stripe_user->getCustomerToken();

                if ($customer_token) {
                    $stripeCustomer = Stripe_Customer::retrieve($customer_token);
                    $cards = $stripeCustomer->cards->data;
                    $card = $cards[$stripe_card];
                    //var_dump($card);
                    //die();
                    $exp_year = $card->exp_year;
                    $exp_month = $card->exp_month;
                    $owner = $card->name;
                    $last = $card->last4;
                    $type = $card->brand;
                }
            }


            Mage::getSingleton('checkout/session')->getQuote()->getPayment()->addData(array(
                'cc_exp_year'  => isset($exp_year) ? $exp_year : null,
                'cc_exp_month' => isset($exp_month) ? $exp_month : null,
                'cc_owner'     => isset($owner) ? $owner : null,
                'cc_last4'     => isset($last) ? $last : null,
                'cc_type'      => isset($type) ? $type : null
            )

            );
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }

        //echo 'asd';
        //die();

        return $this;
    }

    public function getAdminRecipients()
    {
        $recipientEmails = array();
        $emailstring = Mage::getStoreConfig('payment/radweb_stripe/email_receive_contact_form', $this->_storeId);
        $recipientEmails = explode(",", $emailstring);

        return $recipientEmails;
    }

    public function sendError($order, $payment, $error)
    {

        $storeId = $this->_storeId;
        $store = Mage::app()->getStore();

        $paymentBlock = Mage::helper('payment')->getInfoBlock($payment)->setIsSecureMode(true);
        $paymentBlock->getMethod()->setStore($storeId);
        $paymentBlockHtml = $paymentBlock->toHtml();

        $template = Mage::getStoreConfig(self::XML_PATH_SEND_TO_ADMIN_EMAIL, $storeId);
        $mailTemplate = Mage::getModel('core/email_template');

        $recipients = $this->getAdminRecipients();

        $sender = Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId);

        foreach ($recipients as $adminEmail) {
            $recipient['email'] = $adminEmail;
            $recipient['name'] = null;

            $mailTemplate->setDesignConfig(array('area' => 'frontend', 'store' => $storeId))
                         ->sendTransactional($template, $sender, $recipient['email'], $recipient['name'], array(
                             'store'          => $store,
                             'order'          => $order,
                             'payment_html'   => $paymentBlockHtml,
                             'error_message'  => $error,
                             'customer_email' => $order->getCustomerEmail()
                         ));

        }

    }

    public function assignData($data)
    {
        parent::assignData($data);
        $keys = array('stripe_token', 'stripe_card', 'save_stripe_card');
        foreach ($keys as $key) {
            if (is_array($data)) {
                $this->getInfoInstance()->setAdditionalInformation($key, isset($data[$key]) ? $data[$key] : null);
            } elseif ($data instanceof Varien_Object) {
                $this->getInfoInstance()->setAdditionalInformation($key, $data->getData($key));
            }
        }

        return $this;
    }


}