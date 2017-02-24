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

use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Framework\Data\Form as DataForm;
use Unirgy\DropshipBatch\Helper\Data as HelperData;
use Unirgy\DropshipBatch\Model\Source as ModelSource;
use Unirgy\Dropship\Model\Source;

class Form extends Generic
{
    /**
     * @var HelperData
     */
    protected $_bHlp;

    /**
     * @var Source
     */
    protected $_src;

    /**
     * @var ModelSource
     */
    protected $_bSrc;

    public function __construct(
        HelperData $helperData,
        Source $modelSource, 
        ModelSource $dropshipBatchModelSource,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->_bHlp = $helperData;
        $this->_src = $modelSource;
        $this->_bSrc = $dropshipBatchModelSource;

        parent::__construct($context, $registry, $formFactory, $data);
        $this->setDestElementId('batch_form');
    }

    protected function _prepareForm()
    {
        $batch = $this->_coreRegistry->registry('batch_data');
        $hlp = $this->_bHlp;
        $id = $this->getRequest()->getParam('id');
        $form = $this->_formFactory->create();
        $this->setForm($form);

        $fieldset = $form->addFieldset('batch_form', [
            'legend'=>__('Batch Info')
        ]);

        $fieldset->addField('vendor_id', 'note', [
            'name'      => 'vendor_id',
            'label'     => __('Vendor'),
            'text'      => $this->_src->setPath('vendors')->getOptionLabel($batch->getVendorId()),
        ]);

        $fieldset->addField('batch_status', 'select', [
            'name'      => 'batch_status',
            'label'     => __('Status'),
            'disabled'  => true,
            'options'   => $this->_bSrc->setPath('batch_status')->toOptionHash(),
        ]);

        $fieldset->addField('num_rows', 'text', [
            'name'      => 'num_rows',
            'label'     => __('Number of Rows'),
            'disabled'  => true,
        ]);

        $fieldset->addField('notes', 'textarea', [
            'name'      => 'notes',
            'label'     => __('Notes'),
        ]);

        $fieldset->addField('rows_text', 'textarea', [
            'name'      => 'rows_text',
            'readonly'  => true,
            'class'     => 'nowrap',
            'label'     => __('Content'),
        ]);

        if ($batch) {
            $form->setValues($batch->getData());
        }

        return parent::_prepareForm();
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