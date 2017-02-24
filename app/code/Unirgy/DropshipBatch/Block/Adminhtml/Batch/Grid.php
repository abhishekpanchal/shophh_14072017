<?php

namespace Unirgy\DropshipBatch\Block\Adminhtml\Batch;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid as WidgetGrid;
use Magento\Backend\Helper\Data as HelperData;
use Unirgy\DropshipBatch\Model\BatchFactory;
use Unirgy\DropshipBatch\Model\Source as ModelSource;
use Unirgy\Dropship\Model\Source;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var BatchFactory
     */
    protected $_batchFactory;

    /**
     * @var Source
     */
    protected $_src;

    /**
     * @var ModelSource
     */
    protected $_batchSource;


    public function __construct(Context $context, 
        HelperData $backendHelper, 
        BatchFactory $modelBatchFactory, 
        Source $udropshipSource,
        ModelSource $batchSource,
        array $data = [])
    {
        $this->_batchFactory = $modelBatchFactory;
        $this->_src = $udropshipSource;
        $this->_batchSource = $batchSource;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('batchGrid');
        $this->setDefaultSort('batch_id');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('batch_filter');
    }

    protected function _prepareCollection()
    {
        $collection = $this->_batchFactory->create()->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $baseUrl = $this->getUrl();

        $this->addColumn('batch_id', [
            'header'    => __('ID'),
            'index'     => 'batch_id',
            'width'     => 10,
            'type'      => 'number',

        ]);

        $this->addColumn('vendor_id', [
            'header' => __('Vendor'),
            'index' => 'vendor_id',
            'type' => 'options',
            'options' => $this->_src->setPath('vendors')->toOptionHash(),
            'filter' => '\Unirgy\Dropship\Block\Vendor\GridColumnFilter'
        ]);

        $this->addColumn('batch_type', [
            'header' => __('Batch Type'),
            'index' => 'batch_type',
            'type' => 'options',
            'options' => $this->_batchSource->setPath('batch_type')->toOptionHash(),
        ]);

        $this->addColumn('batch_status', [
            'header' => __('Batch Status'),
            'index' => 'batch_status',
            'type' => 'options',
            'options' => $this->_batchSource->setPath('batch_status')->toOptionHash(),
            'renderer'  => '\Unirgy\DropshipBatch\Block\Adminhtml\Dist\Grid\Status',
        ]);

        $this->addColumn('num_rows', [
            'header'    => __('# of Rows'),
            'index'     => 'num_rows',
            'type'      => 'number',
        ]);

        $this->addColumn('created_at', [
            'header'    => __('Created At'),
            'index'     => 'created_at',
            'type'      => 'datetime',
            'width'     => 150,
        ]);

        $this->addColumn('updated_at', [
            'header'    => __('Updated At'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
            'width'     => 150,
        ]);

        $this->addColumn('scheduled_at', [
            'header'    => __('Scheduled At'),
            'index'     => 'scheduled_at',
            'type'      => 'datetime',
            'width'     => 150,
        ]);

        $this->addExportType('*/*/exportCsv', __('CSV'));
        $this->addExportType('*/*/exportXml', __('XML'));

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('batch_id');
        $this->getMassactionBlock()->setFormFieldName('batch');

        $this->getMassactionBlock()->addItem('delete', [
             'label'=> __('Delete'),
             'url'  => $this->getUrl('*/*/massDelete'),
             'confirm' => __('Are you sure?')
        ]);

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current'=>true]);
    }
}
