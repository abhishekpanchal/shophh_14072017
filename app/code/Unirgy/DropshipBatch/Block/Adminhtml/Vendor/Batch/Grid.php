<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipBatch\Block\Adminhtml\Vendor\Batch;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid as WidgetGrid;
use Magento\Backend\Helper\Data as HelperData;
use Magento\Framework\Registry;
use Unirgy\DropshipBatch\Model\BatchFactory;
use Unirgy\DropshipBatch\Model\Source;
use Unirgy\Dropship\Model\VendorFactory;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Registry
     */
    protected $_frameworkRegistry;

    /**
     * @var VendorFactory
     */
    protected $_modelVendorFactory;

    /**
     * @var BatchFactory
     */
    protected $_modelBatchFactory;

    /**
     * @var Source
     */
    protected $_modelSource;

    public function __construct(Context $context, 
        HelperData $backendHelper, 
        Registry $frameworkRegistry, 
        VendorFactory $modelVendorFactory, 
        BatchFactory $modelBatchFactory, 
        Source $modelSource, 
        array $data = [])
    {
        $this->_frameworkRegistry = $frameworkRegistry;
        $this->_modelVendorFactory = $modelVendorFactory;
        $this->_modelBatchFactory = $modelBatchFactory;
        $this->_modelSource = $modelSource;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('udbatch_vendor_batches');
        $this->setDefaultSort('batch_id');
        $this->setUseAjax(true);
    }

    public function getVendor()
    {
        $batch = $this->_frameworkRegistry->registry('vendor_data');
        if (!$batch) {
            $batch = $this->_modelVendorFactory->create()->load($this->getBatchId());
            $this->_frameworkRegistry->register('vendor_data', $batch);
        }
        return $batch;
    }

    protected function _prepareCollection()
    {
        $collection = $this->_modelBatchFactory->create()->getCollection()
            ->addFieldToFilter('vendor_id', $this->getVendor()->getId());
        ;

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('batch_id', [
            'header'    => __('ID'),
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

        $this->addColumn('batch_status', [
            'header' => __('Batch Status'),
            'index' => 'batch_status',
            'type' => 'options',
            'options' => $this->_modelSource->setPath('batch_status')->toOptionHash(),
        ]);

        $this->addColumn('num_rows', [
            'header'    => __('# of Rows'),
            'index'     => 'num_rows',
            'type'      => 'number',
        ]);

        $this->addColumn('scheduled_at', [
            'header'    => __('Scheduled At'),
            'index'     => 'scheduled_at',
            'type'      => 'datetime',
            'width'     => 150,
        ]);

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('udbatch/batch/edit', ['id' => $row->getId()]);
    }

    public function getGridUrl()
    {
        return $this->getUrl('udbatch/batch/vendorBatchesGrid', ['_current'=>true]);
    }
}
