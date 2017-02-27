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
use Unirgy\DropshipBatch\Helper\Data as HelperData;
use Unirgy\DropshipBatch\Model\Source;
use Unirgy\Dropship\Model\Source as ModelSource;

class Invimport extends \Unirgy\Dropship\Block\Adminhtml\Widget\Form
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

        $fieldset = $form->addFieldset('batch_form', ['legend'=>__('Batch Info')]);
        $this->_addElementTypes($fieldset);

        $fieldset->addField('vendor_id', 'udropship_vendor', [
            'name'      => 'vendor_id',
            'label'     => __('Vendor'),
            'class'     => 'required-entry',
            'required'  => true,
        ]);

        $fieldset->addField('batch_type', 'hidden', [
            'name'      => 'batch_type',
            'value'     => 'import_inventory',
        ]);
        $fieldset->addField('batch_notes', 'textarea', [
            'name'      => 'batch_notes',
            'label'     => __('Batch notes'),
        ]);
        $fieldset->addField('use_custom_template', 'select', [
            'name'      => 'use_custom_template',
            'label'     => __('Use Template'),
            'options'   => $this->_bSrc->setPath('use_custom_template')->toOptionHash(),
        ]);

        $fieldset = $form->addFieldset('default_form', ['legend'=>__("Import from vendor's default locations")]);

        $fieldset->addField('import_inventory_default', 'select', [
            'name'      => 'import_inventory_default',
            'label'     => __('Default locations'),
            'options'   => $this->_src->setPath('yesno')->toOptionHash(),
        ]);

        $fieldset = $form->addFieldset('upload_form', ['legend'=>__('Import from uploaded file')]);

        $fieldset->addField('import_inventory_upload', 'file', [
            'name'      => 'import_inventory_upload',
            'label'     => __('Upload file'),
        ]);

        $fieldset = $form->addFieldset('textarea_form', ['legend'=>__('Import from pasted content')]);

        $fieldset->addField('import_inventory_textarea', 'textarea', [
            'name'      => 'import_inventory_textarea',
            'label'     => __('Paste content'),
        ]);

        $fieldset = $form->addFieldset('locations_form', ['legend'=>__('Import from custom locations')]);

        $fieldset->addField('import_inventory_locations', 'textarea', [
            'name'      => 'import_inventory_locations',
            'label'     => __('Custom locations'),
            'note'      => __('Use <a href="http://unirgy.com/wiki/udropship/batch/reference" target="udbatch_reference">reference</a> for location format, separate multiple locations with new line'),
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