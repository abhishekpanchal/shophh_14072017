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
use Unirgy\DropshipBatch\Model\Batch\DistFactory;
use Unirgy\DropshipBatch\Model\Source;
use Unirgy\DropshipBatch\Model\BatchFactory;

class Dist extends \Magento\Backend\Block\Widget\Grid\Extended implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var Registry
     */
    protected $_registry;

    /**
     * @var BatchFactory
     */
    protected $_batchFactory;

    /**
     * @var DistFactory
     */
    protected $_distFactory;

    /**
     * @var Source
     */
    protected $_modelSource;

    public function __construct(Context $context, 
        HelperData $backendHelper, 
        Registry $frameworkRegistry,
        BatchFactory $batchFactory,
        DistFactory $batchDistFactory, 
        Source $modelSource, 
        array $data = [])
    {
        $this->_registry = $frameworkRegistry;
        $this->_batchFactory = $batchFactory;
        $this->_distFactory = $batchDistFactory;
        $this->_modelSource = $modelSource;

        parent::__construct($context, $backendHelper, $data);
        $this->setId('udbatch_batch_dist');
        $this->setDefaultSort('dist_id');
        $this->setUseAjax(true);
    }

    public function getBatch()
    {
        $batch = $this->_registry->registry('batch_data');
        if (!$batch) {
            $batch = $this->_batchFactory->create()->load($this->getBatchId());
            $this->_registry->register('batch_data', $batch);
        }
        return $batch;
    }

    protected function _prepareCollection()
    {
        $collection = $this->_distFactory->create()->getCollection()
            ->addFieldToFilter('batch_id', $this->getBatch()->getId());
        ;

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('dist_id', [
            'header'    => __('ID'),
            'sortable'  => true,
            'width'     => '60',
            'index'     => 'dist_id'
        ]);
        $this->addColumn('location', [
            'header'    => __('Location'),
            'index'     => 'location'
        ]);
        $this->addColumn('dist_status', [
            'header'    => __('Status'),
            'index'     => 'dist_status',
            'type'      => 'options',
            'options'   => $this->_modelSource->setPath('dist_status')->toOptionHash(),
            'renderer'  => '\Unirgy\DropshipBatch\Block\Adminhtml\Dist\Grid\Status',
        ]);
        $this->addColumn('error_info', [
            'header'    => __('Error'),
            'index'     => 'error_info'
        ]);
        $this->addColumn('updated_at', [
            'header'    => __('Updated At'),
            'index'     => 'updated_at',
            'type'      => 'datetime',
        ]);
        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/distGrid', ['_current'=>true]);
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
