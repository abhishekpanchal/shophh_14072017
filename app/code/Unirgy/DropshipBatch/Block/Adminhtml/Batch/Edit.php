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

namespace Unirgy\DropshipBatch\Block\Adminhtml\Batch;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Form\Container;
use Magento\Framework\Registry;
use Unirgy\DropshipBatch\Model\BatchFactory;

class Edit extends Container
{
    /**
     * @var BatchFactory
     */
    protected $_batchFactory;

    /**
     * @var Registry
     */
    protected $_registry;

    public function __construct(Context $context, 
        BatchFactory $modelBatchFactory, 
        Registry $frameworkRegistry, 
        array $data = [])
    {
        $this->_batchFactory = $modelBatchFactory;
        $this->_registry = $frameworkRegistry;
        parent::__construct($context, $data);
        $this->setData('form_action_url', $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))));
    }

    public function _construct()
    {
        parent::_construct();
        $this->_objectId = 'id';
        $this->_blockGroup = 'Unirgy_DropshipBatch';
        $this->_controller = 'adminhtml_batch';

        if ($this->getRequest()->getParam($this->_objectId)) {
            $this->removeButton('save');
            $this->updateButton('delete', 'label', __('Delete Batch'));
            $model = $this->_batchFactory->create()
                ->load($this->getRequest()->getParam($this->_objectId));
            $this->_registry->register('batch_data', $model);
        } else {
            $this->updateButton('save', 'label', __('Create Batch(es)'));
        }
    }

}
