<?php


/**
 * Stripe observer
 *
 * @category    Radweb
 * @package     Radweb_Stripe
 * @author      Artur Salagean <info@radweb.co.uk>
 * @copyright   Radweb (http://radweb.co.uk)
 * 
 */

class Radweb_Stripe_Model_Observer
{


    public function addColumnToSalesOrderGrid($observer) {

        $block = $observer->getEvent()->getBlock();
        //if (get_class($block) == 'Mage_Adminhtml_Block_Sales_Order_Grid') {
        if($block instanceof Mage_Adminhtml_Block_Sales_Order_Grid) { //Thanks Paul Ketelle for your feedback on this

            $block->addColumnAfter('radweb_transaction_state', array(
                'header' => Mage::helper('radweb_stripe')->__('Stripe'),
                'index' => 'radweb_transaction_state',
                'align' => 'center',
                'filter' => false,
                'renderer' => 'radweb_stripe/adminhtml_sales_order_grid_renderer_state',
                'sortable' => false,
                    )
                    , 'real_order_id');
        }

        return $observer;
    }



}
