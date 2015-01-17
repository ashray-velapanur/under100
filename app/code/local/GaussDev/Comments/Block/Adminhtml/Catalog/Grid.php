<?php

class GaussDev_Comments_Block_Adminhtml_Catalog_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('entity_id');
        $this->setId('gaussdev_comments_grid');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setUseMassaction(false);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('created_at', array(
            'header'       => 'Created At',
            'index'        => 'created_at',
            'type'         => 'datetime',
            'width'        => '50px',
            'filter_index' => 'main_table.created_at'
        ));
        $this->addColumn('entity_id', array(
            'header'       => 'ID',
            'align'        => 'right',
            'index'        => 'entity_id',
            'type'         => 'number',
            'width'        => '50px',
            'filter_index' => 'main_table.entity_id'
        ));
        $this->addColumn('parent_id', array(
            'header'       => 'Parent Comment ID',
            'align'        => 'right',
            'width'        => '50px',
            'default'      => 'Null',
            'index'        => 'parent_id',
            'type'         => 'number',
            'filter_index' => 'main_table.parent_id'
        ));
        $this->addColumn('product_id', array(
            'header'       => 'Product ID',
            'align'        => 'right',
            'index'        => 'product_id',
            'type'         => 'number',
            'width'        => '50px',
            'filter_index' => 'main_table.product_id'
        ));
        $this->addColumn('customer_id', array(
            'header'       => 'Customer ID',
            'align'        => 'right',
            'width'        => '50px',
            'index'        => 'customer_id',
            'type'         => 'number',
            'filter_index' => 'main_table.customer_id'
        ));
        $this->addColumn('reports', array(
            'header'       => 'Reports',
            'width'        => '50px',
            'index'        => 'reports',
            'type'         => 'number',
            'filter_index' => 'main_table.reports'
        ));
        $this->addColumn('likes', array(
            'header'       => 'Likes',
            'width'        => '50px',
            'index'        => 'likes',
            'type'         => 'number',
            'filter_index' => 'main_table.likes'
        ));
        $this->addColumn('is_deleted', array(
            'header'       => 'Deleted',
            'width'        => '50px',
            'index'        => 'is_deleted',
            'type'         => 'options',
            'filter_index' => 'main_table.is_deleted',
            'renderer'     => 'GaussDev_Comments_Block_Adminhtml_Widget_Grid_Column_Renderer_Yesno',
            'options'      => array('0' => 'No', '1' => 'Yes')
        ));
        $this->addColumn('is_spam', array(
            'header'       => 'Spam',
            'width'        => '50px',
            'index'        => 'is_spam',
            'type'         => 'options',
            'filter_index' => 'main_table.is_spam',
            'renderer'     => 'GaussDev_Comments_Block_Adminhtml_Widget_Grid_Column_Renderer_Yesno',
            'options'      => array('0' => 'No', '1' => 'Yes')
        ));
        $this->addColumn('message', array(
            'header'                    => 'Comment Message',
            'type'                      => 'text',
            'sortable'                  => false,
            'index'                     => 'message',
            'escape'                    => true,
            'renderer'                  => 'GaussDev_Comments_Block_Adminhtml_Widget_Grid_Column_Renderer_Alert'
        ));
        $this->addColumn('change_spam', array(
            'header'   => Mage::helper('gaussdev_comments')->__('Switch'),
            'sortable' => false,
            'type'     => 'action',
            'getter'   => 'getId',
            'actions'  => array(
                array(
                    'caption' => Mage::helper('gaussdev_comments')->__('Spam'),
                    'url'     => array('base' => '*/*/spam'),
                    'field'   => 'id'
                )
            ),
            'filter'   => false,
            'index'    => 'entity_id'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return null;
    }

    protected function _getCollectionClass()
    {
        return 'gaussdev_comments/comment_collection';
    }


}
