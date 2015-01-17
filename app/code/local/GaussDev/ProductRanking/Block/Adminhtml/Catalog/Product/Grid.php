<?php

class GaussDev_ProductRanking_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{

    protected function _prepareCollection()
    {
        parent::_prepareCollection();
        $this->getCollection()->addAttributeToSelect('popularityscore');

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('popularityscore', array(
                'header' => Mage::helper('catalog')->__('Popularity'),
                'width'  => '75px',
                'type'   => 'number',
                'index'  => 'popularityscore',
                'default'=> '0'
            ));

        return parent::_prepareColumns();
    }
}