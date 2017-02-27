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

namespace Unirgy\DropshipBatch\Block\Adminhtml\Batch\Edit\Tab;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Helper\Data as HelperData;
use Magento\Framework\Registry;
use Unirgy\DropshipBatch\Model\Batch\InvrowFactory;
use Unirgy\DropshipBatch\Model\Batch\RowFactory;
use Unirgy\DropshipBatch\Model\BatchFactory;

class Rows extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var Registry
     */
    protected $_frameworkRegistry;

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

    public function __construct(Context $context, 
        HelperData $backendHelper, 
        Registry $frameworkRegistry,
        BatchFactory $batchFactory,
        InvrowFactory $batchInvrowFactory, 
        RowFactory $batchRowFactory, 
        array $data = [])
    {
        $this->_frameworkRegistry = $frameworkRegistry;
        $this->_batchFactory = $batchFactory;
        $this->_batchInvrowFactory = $batchInvrowFactory;
        $this->_batchRowFactory = $batchRowFactory;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('udbatch_batch_rows');
        $this->setDefaultSort('row_id');
        $this->setUseAjax(true);
    }

    public function getBatch()
    {
        $batch = $this->_frameworkRegistry->registry('batch_data');
        if (!$batch) {
            $batch = $this->_batchFactory->create()->load($this->getBatchId());
            $this->_frameworkRegistry->register('batch_data', $batch);
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
            'index'     => 'entity_id'
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
        } else {
        	$this->addColumn('order_increment_id', [
	            'header'    => __('Order ID'),
	            'index'     => 'order_increment_id'
	        ]);
	        $this->addColumn('po_increment_id', [
	            'header'    => __('PO ID'),
	            'index'     => 'po_increment_id'
	        ]);
        }
        $this->addColumn('has_error', [
            'header'    => __('Has error'),
            'index'     => 'has_error'
        ]);
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/rowGrid', ['_current'=>true]);
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
