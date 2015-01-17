<?php

class GaussDev_CustomerImages_CustomerController extends Mage_Core_Controller_Front_Action
{
    public function editPostAction()
    {
        if (!$this->_validateFormKey()) {
            return $this->_redirect('customer/account/edit');
        }

        if ($this->getRequest()->isPost()) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = $this->_getSession()->getCustomer();

            /** @var $customerForm Mage_Customer_Model_Form */
            $customerForm = $this->_getModel('customer/form');
            $customerForm->setFormCode('customer_account_edit')->setEntity($customer);

            $customerData = $customerForm->extractData($this->getRequest());

            $DD = $this->getRequest()->getPost('DD');
            $MM = $this->getRequest()->getPost('MM');
            $YYYY = $this->getRequest()->getPost('YYYY');
            $dob = ($DD && $MM && $YYYY) ? "{$YYYY}-{$MM}-{$DD}" : false;

            if ($dob) {
                $customerData['dob'] = $dob;
            }
            $prefix = $this->getRequest()->getPost('prefix');

            $errors = array();
            $customerErrors = $customerForm->validateData($customerData);
            if ($customerErrors !== true) {
                $errors = array_merge($customerErrors, $errors);
            } else {
                $customerForm->compactData($customerData);
                if ($prefix) {
                    $customerForm->getEntity()->setPrefix($prefix)->save();
                }
                $errors = array();

                // If password change was requested then add it to common validation scheme
                if ($this->getRequest()->getParam('change_password')) {
                    $currPass = $this->getRequest()->getPost('current_password');
                    $newPass = $this->getRequest()->getPost('password');
                    $confPass = $this->getRequest()->getPost('confirmation');

                    $oldPass = $this->_getSession()->getCustomer()->getPasswordHash();
                    if ($customer->getHasRandomPassword()) {
                        if (strlen($newPass)) {
                            $customer->setPassword($newPass);
                            $customer->setConfirmation($confPass);
                            $customer->setHasRandomPassword(0);
                        } else {
                            $errors[] = $this->__('New password field cannot be empty.');
                        }
                    } else {
                        if ($this->_getHelper('core/string')->strpos($oldPass, ':')) {
                            list($_salt, $salt) = explode(':', $oldPass);
                        } else {
                            $salt = false;
                        }

                        if ($customer->hashPassword($currPass, $salt) == $oldPass) {
                            if (strlen($newPass)) {
                                /**
                                 * Set entered password and its confirmation - they
                                 * will be validated later to match each other and be of right length
                                 */
                                $customer->setPassword($newPass);
                                $customer->setConfirmation($confPass);
                            } else {
                                $errors[] = $this->__('New password field cannot be empty.');
                            }
                        } else {
                            $errors[] = $this->__('Invalid current password');
                        }
                    }
                }

                // Validate account and compose list of errors if any
                $customerErrors = $customer->validate();
                if (is_array($customerErrors)) {
                    $errors = array_merge($errors, $customerErrors);
                }
            }

            if (!empty($errors)) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost());
                foreach ($errors as $message) {
                    $this->_getSession()->addError($message);
                }
                $this->_redirect('customer/account/edit');

                return $this;
            }

            try {
                $customer->setConfirmation(null);
                $customer->save();
                $this->_getSession()
                     ->setCustomer($customer)
                     ->addSuccess($this->__('The account information has been saved.'));

                $this->_redirect('customer/account');

                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->setCustomerFormData($this->getRequest()->getPost())->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()
                     ->setCustomerFormData($this->getRequest()->getPost())
                     ->addException($e, $this->__('Cannot save the customer.'));
            }
        }

        $this->_redirect('customer/account/edit');
    }

    /**
     * Retrieve customer session model object
     *
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /**
     * Get model by path
     *
     * @param string     $path
     * @param array|null $arguments
     *
     * @return false|Mage_Core_Model_Abstract
     */
    public function _getModel($path, $arguments = array())
    {
        return Mage::getModel($path, $arguments);
    }

    /**
     * Get Helper
     *
     * @param string $path
     *
     * @return Mage_Core_Helper_Abstract
     */
    protected function _getHelper($path)
    {
        return Mage::helper($path);
    }

    public function deleteAddressAction()
    {
        $addressId = $this->getRequest()->getParam('id', false);

        if ($addressId) {
            $address = Mage::getModel('customer/address')->load($addressId);

            // Validate address_id <=> customer_id
            if ($address->getCustomerId() != Mage::getSingleton('customer/session')->getCustomerId()) {
                Mage::getSingleton('customer/session')
                    ->addError($this->__('The address does not belong to this customer.'));
                $this->_redirect(null, array('_direct' => 'customer/accountpage'));

                return;
            }

            try {
                $address->delete();
                Mage::getSingleton('customer/session')->addSuccess($this->__('The address has been deleted.'));
            } catch (Exception $e) {
                Mage::getSingleton('customer/session')
                    ->addException($e, $this->__('An error occurred while deleting the address.'));
            }
        }
        $this->_redirect(null, array('_direct' => 'customer/accountpage'));
    }

}