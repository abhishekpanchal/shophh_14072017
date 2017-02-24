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

use Magento\Framework\Data\Form as DataForm;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipBatch\Helper\Data as HelperData;
use Unirgy\DropshipBatch\Model\Source;
use Unirgy\Dropship\Model\Source as ModelSource;

class Import extends \Unirgy\Dropship\Block\Adminhtml\Widget\Form
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var HelperData
     */
    protected $_bHlp;

    /**
     * @var Source
     */
    protected $_bSrc;

    /**
     * @var ModelSource
     */
    protected $_src;

    public function __construct(
        HelperData $helperData,
        Source $modelSource,
        ModelSource $dropshipModelSource,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    )
    {
        $this->_bHlp = $helperData;
        $this->_bSrc = $modelSource;
        $this->_src = $dropshipModelSource;

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

        $batchType = $this->getRequest()->getParam('type');

        $fieldset = $form->addFieldset('batch_form', ['legend'=>__('Batch Info')]);
        $this->_addElementTypes($fieldset);

        if ($this->_scopeConfig->isSetFlag('udropship/batch/allow_all_vendors_import', ScopeInterface::SCOPE_STORE)) {
            $vendorField = 'vendors_import_orders';
        } else {
            $vendorField = 'udropship_vendor';
        }

        $fieldset->addField('vendor_id', $vendorField, [
            'name'      => 'vendor_id',
            'label'     => __('Vendor'),
            'class'     => 'required-entry',
            'required'  => true,
        ]);

        $fieldset->addField('batch_type', 'hidden', [
            'name'      => 'batch_type',
            'value'     => $batchType,
        ]);
        $fieldset->addField('use_custom_template', 'select', [
            'name'      => 'use_custom_template',
            'label'     => __('Use Template'),
            'options'   => $this->_bSrc->setPath('use_custom_template')->toOptionHash(),
        ]);

        $fieldset = $form->addFieldset('default_form', ['legend'=>__("Import from vendor's default locations")]);

        $fieldset->addField("{$batchType}_default", 'select', [
            'name'      => "{$batchType}_default",
            'label'     => __('Default locations'),
            'options'   => $this->_src->setPath('yesno')->toOptionHash(),
        ]);

        $fieldset = $form->addFieldset('upload_form', ['legend'=>__('Import from uploaded file')]);

        $fieldset->addField("{$batchType}_upload", 'file', [
            'name'      => "{$batchType}_upload",
            'label'     => __('Upload file'),
        ]);

        $fieldset = $form->addFieldset('textarea_form', ['legend'=>__('Import from pasted content')]);

        $fieldset->addField("{$batchType}_textarea", 'textarea', [
            'name'      => "{$batchType}_textarea",
            'label'     => __('Paste content'),
        ]);

        $fieldset = $form->addFieldset('locations_form', ['legend'=>__('Import from custom locations')]);

        $fieldset->addField("{$batchType}_locations", 'textarea', [
            'name'      => "{$batchType}_locations",
            'label'     => __('Custom locations'),
            'note'      => __('Use <a href="http://unirgy.com/wiki/udropship/batch/reference" target="udbatch_reference">reference</a> for location format, separate multiple locations with new line'),
        ]);

        if ($batch) {
            $form->setValues($batch->getData());
        }

        return parent::_prepareForm();
    }

    protected function _getAdditionalElementTypes()
    {
        return array_merge(parent::_getAdditionalElementTypes(), [
            'vendors_import_orders'=>'\Unirgy\DropshipBatch\Block\Vendor\Htmlselect'
        ]);
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