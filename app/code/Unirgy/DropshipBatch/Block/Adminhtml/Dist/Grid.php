<?php

namespace Unirgy\DropshipBatch\Block\Adminhtml\Dist;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid\Extended as WidgetGrid;
use Magento\Backend\Helper\Data as HelperData;
use Unirgy\DropshipBatch\Model\Batch\DistFactory;
use Unirgy\DropshipBatch\Model\Source;
use Unirgy\Dropship\Model\Source as ModelSource;

class Grid extends WidgetGrid
{
    /**
     * @var DistFactory
     */
    protected $_batchDistFactory;

    /**
     * @var Source
     */
    protected $_modelSource;

    /**
     * @var ModelSource
     */
    protected $_dropshipModelSource;

    /**
     * @var \Unirgy\Dropship\Helper\Data
     */
    protected $_hlp;


    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        DistFactory $batchDistFactory,
        Source $modelSource,
        ModelSource $dropshipModelSource,
        Context $context,
        HelperData $backendHelper, 
        array $data = []
    )
    {
        $this->_hlp = $udropshipHelper;
        $this->_batchDistFactory = $batchDistFactory;
        $this->_modelSource = $modelSource;
        $this->_dropshipModelSource = $dropshipModelSource;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('distGrid');
        $this->setDefaultSort('dist_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('dist_filter');
    }

    protected function _prepareCollection()
    {
        $collection = $this->_batchDistFactory->create()->getCollection();

        $res = $this->_hlp->rHlp();
        $collection->getSelect()
            ->join(['b'=>$res->getTableName('udropship_batch')], 'b.batch_id=main_table.batch_id', ['batch_type', 'vendor_id', 'num_rows', 'batch_created_at'=>'created_at']);

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();

        $this->addColumn('dist_id', [
            'header'    => __('Location ID'),
            'index'     => 'dist_id',
            'width'     => 10,
            'type'      => 'number',
        ]);

        $this->addColumn('batch_id', [
            'header'    => __('Batch ID'),
            'index'     => 'batch_id',
            'width'     => 10,
            'type'      => 'number',
        ]);

        $this->addColumn('batch_type', [
            'header' => __('Batch Type'),
            'index' => 'batch_type',
            'type' => 'options',
            'options' => $this->_modelSource->setPath('batch_type')->toOptionHash(),
        ]);

        $this->addColumn('num_rows', [
            'header'    => __('# of Rows'),
            'index'     => 'num_rows',
            'type'      => 'number',
        ]);

        $this->addColumn('vendor_id', [
            'header' => __('Vendor'),
            'index' => 'vendor_id',
            'type' => 'options',
            'options' => $this->_dropshipModelSource->setPath('vendors')->toOptionHash(),
            'filter' => '\Unirgy\Dropship\Block\Vendor\GridColumnFilter'
        ]);

        $this->addColumn('location', [
            'header'    => __('Location'),
            'index'     => 'location',
        ]);

        $this->addColumn('batch_created_at', [
            'header'    => __('Batch Created At'),
            'index'     => 'batch_created_at',
            'filter_index' => 'b.created_at',
            'type'      => 'datetime',
            'width'     => 150,
        ]);

        $this->addColumn('updated_at', [
            'header'    => __('Hist Updated At'),
            'index'     => 'updated_at',
            'filter_index' => 'main_table.updated_at',
            'type'      => 'datetime',
            'width'     => 150,
        ]);

        $this->addColumn('dist_status', [
            'header' => __('Status'),
            'index' => 'dist_status',
            'type' => 'options',
            'options' => $this->_modelSource->setPath('dist_status')->toOptionHash(),
            'renderer'  => '\Unirgy\DropshipBatch\Block\Adminhtml\Dist\Grid\Status',
        ]);

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('dist_id');
        $this->getMassactionBlock()->setFormFieldName('dist');

        $this->getMassactionBlock()->addItem('retry', [
             'label'=> __('Retry'),
             'url'  => $this->getUrl('*/*/massRetry'),
             'confirm' => __('Are you sure?')
        ]);

        $this->getMassactionBlock()->addItem('status', [
             'label'=> __('Change status'),
             'url'  => $this->getUrl('*/*/massStatus', ['_current'=>true]),
             'additional' => [
                'status' => [
                     'name' => 'status',
                     'type' => 'select',
                     'class' => 'required-entry',
                     'label' => __('Status'),
                     'values' => $this->_modelSource->setPath('dist_status')->toOptionArray(true),
                 ]
             ]
        ]);

        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current'=>true]);
    }
}
