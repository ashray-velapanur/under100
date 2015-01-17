<?php

class GaussDev_Comments_Block_Adminhtml_Catalog_GridContainer extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'comments';
        $this->_controller = 'adminhtml_grid';
        $this->_headerText = $this->__('Product Comments');
        parent::__construct();
        $this->_removeButton('add');
    }
}
