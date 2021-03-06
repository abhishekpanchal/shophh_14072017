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

namespace Unirgy\DropshipBatch\Block\Adminhtml\Batch\Edit\Tab\Import;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Helper\Data as HelperData;
use Magento\Framework\Registry;
use Unirgy\DropshipBatch\Model\BatchFactory;
use Unirgy\DropshipBatch\Model\Batch\InvrowFactory;
use Unirgy\DropshipBatch\Model\Batch\RowFactory;
use Unirgy\DropshipBatch\Model\Source as ModelSource;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Unirgy\Dropship\Model\Source as DropshipModelSource;

class Rows extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var BatchFactory
     */
    protected $_batchFactory;

    /**
     * @var InvrowFactory
     */
    protected $_batchInvrowFactory;

    /**
     * @var RowFactory
     */
    protected $_batchRowFactory;

    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    /**
     * @var ModelSource
     */
    protected $_bSrc;

    /**
     * @var DropshipModelSource
     */
    protected $_src;

    public function __construct(Context $context, 
        HelperData $backendHelper, 
        Registry $frameworkRegistry, 
        BatchFactory $modelBatchFactory, 
        InvrowFactory $batchInvrowFactory, 
        RowFactory $batchRowFactory, 
        DropshipHelperData $helperData, 
        ModelSource $dropshipBatchModelSource,
        DropshipModelSource $dropshipModelSource,
        array $data = [])
    {
        $this->_coreRegistry = $frameworkRegistry;
        $this->_batchFactory = $modelBatchFactory;
        $this->_batchInvrowFactory = $batchInvrowFactory;
        $this->_batchRowFactory = $batchRowFactory;
        $this->_hlp = $helperData;
        $this->_bSrc = $dropshipBatchModelSource;
        $this->_src = $dropshipModelSource;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('udbatch_batch_rows');
        $this->setDefaultSort('row_id');
        $this->setUseAjax(true);
    }

    public function getBatch()
    {
        $batch = $this->_coreRegistry->registry('batch_data');
        if (!$batch) {
            $batch = $this->_batchFactory->create()->load($this->getBatchId());
            $this->_coreRegistry->register('batch_data', $batch);
        }
        return $batch;
    }

    protected function _prepareCollection()
    {
    	if (in_array($this->getBatch()->getBatchType(), ['import_inventory', 'export_inventory'])) {
            $collection = $this->_batchInvrowFactory->create()->getCollection()
            	->addFieldToFilter('batch_id', $this->getBatch()->getId());
        } else {
        	$collection = $this->_batchRowFactory->create()->getCollection()
            	->addFieldToFilter('batch_id', $this->getBatch()->getId());
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('row_id', [
            'header'    => __('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'row_id'
        ]);
        if (in_array($this->getBatch()->getBatchType(), ['import_inventory', 'export_inventory'])) {
            $this->addColumn('sku', [
	            'header'    => __('Sku'),
	            'index'     => 'sku'
	        ]);
	        $this->addColumn('vendor_cost', [
	            'header'    => __('Cost'),
	            'index'     => 'vendor_cost'
	        ]);
	        $this->addColumn('stock_qty', [
	            'header'    => __('Stock Qty'),
	            'index'     => 'stock_qty'
	        ]);
            $this->addColumn('stock_qty_add', [
	            'header'    => __('Stock Qty Add'),
	            'index'     => 'stock_qty_add'
	        ]);
	        $this->addColumn('vendor_sku', [
	            'header'    => __('Vendor Sku'),
	            'index'     => 'vendor_sku'
	        ]);
            if ($this->_hlp->isUdmultiAvailable()) {
                $this->addColumn('status', [
                    'header'    => __('Status'),
                    'index'     => 'status',
                    'type'      => 'options',
                    'options'   => $this->_hlp->getObj('Unirgy\DropshipMulti\Model\Source')->setPath('vendor_product_status')->toOptionHash(),
                ]);
            } else {
                $this->addColumn('stock_status', [
                    'header'    => __('Stock Status'),
                    'index'     => 'stock_status',
                    'type'      => 'options',
                    'options'   => $this->_bSrc->setPath('stock_status')->toOptionHash(),
                ]);
            }
            if ($this->_hlp->isUdmultiPriceAvailable()) {
                $this->addColumn('state', [
                    'header'    => __('State'),
                    'index'     => 'state',
                    'type'      => 'options',
                    'options'   => $this->_hlp->getObj('Unirgy\DropshipMultiPrice\Model\Source')->setPath('vendor_product_state')->toOptionHash(),
                ]);
                $this->addColumn('vendor_price', [
                    'header'    => __('Vendor Price'),
                    'index'     => 'vendor_price'
                ]);
                $this->addColumn('special_price', [
                    'header'    => __('Special Price'),
                    'index'     => 'special_price'
                ]);
                $this->addColumn('special_from_date', [
                    'header'    => __('Special From'),
                    'type'      => 'date',
                    'index'     => 'special_from_date'
                ]);
                $this->addColumn('special_to_date', [
                    'header'    => __('Special To'),
                    'type'      => 'date',
                    'index'     => 'special_to_date'
                ]);
            }
        } else {
	        $this->addColumn('order_increment_id', [
	            'header'    => __('Order ID'),
	            'index'     => 'order_increment_id'
	        ]);
	        $this->addColumn('po_increment_id', [
	            'header'    => __('PO ID'),
	            'index'     => 'po_increment_id'
	        ]);
	        $this->addColumn('tracking_id', [
	            'header'    => __('Tracking ID'),
	            'index'     => 'tracking_id'
	        ]);
        }
        $this->addColumn('has_error', [
            'header'    => __('Has error'),
            'index'     => 'has_error',
            'type'      => 'options',
            'options'   => $this->_src->setPath('yesno')->toOptionHash(),
        ]);
        $this->addColumn('error_info', [
            'header'    => __('Error Info'),
            'index'     => 'error_info',
        ]);
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/importRowGrid', ['_current'=>true, 'id'=>$this->_coreRegistry->registry('batch_data')->getId(), 'type'=>'import_orders']);
    }

    public function getTabLabel()
    {
        return $this->getData('label');
    }
    public function getTabTitle()
    {
        return $this->getData('title');
    }
    public function canShowTab()
    {
        return true;
    }
    public function isHidden()
    {
        return false;
    }
}
