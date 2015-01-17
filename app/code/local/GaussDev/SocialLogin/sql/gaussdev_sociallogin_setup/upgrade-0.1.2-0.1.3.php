<?php
$vatAttribute = Mage::getSingleton('eav/config')->getAttribute('customer_address', 'address_name');
$vatAttribute->setData('used_in_forms', array(
    'adminhtml_customer_address',
    'customer_address_edit',
    'customer_register_address'
));
$vatAttribute->save();