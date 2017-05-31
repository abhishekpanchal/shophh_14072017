<?php

namespace Altima\Lookbookslider\Block\Adminhtml\Slide\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

class Products extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface 
{

	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }
    
    protected function _prepareForm()
    {
    	$model             = $this->_coreRegistry->registry('current_model');

        //$isElementDisabled = !$this->_isAllowedAction('Altima_Lookbookslider::slide');
        if ($this->_isAllowedAction('Altima_Lookbookslider::slide')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('slide_section_products_');

        $fieldset = $form->addFieldset('section_products_fieldset', [
            'legend' => __('Products'),
            'class'  => 'fieldset-wide'
            ]
        );

        $fieldset->addField(
            'collection_title',
            'text',
            [
                'name' => 'collection_title',
                'label' => __('Collection Title'),
                'title' => __('Collection Title'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'sku_one',
            'text',
            [
                'name' => 'sku_one',
                'label' => __('SKU One'),
                'title' => __('SKU One'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'sku_two',
            'text',
            [
                'name' => 'sku_two',
                'label' => __('SKU Two'),
                'title' => __('SKU Two'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'sku_three',
            'text',
            [
                'name' => 'sku_three',
                'label' => __('SKU Three'),
                'title' => __('SKU Three'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'sku_four',
            'text',
            [
                'name' => 'sku_four',
                'label' => __('SKU Four'),
                'title' => __('SKU Four'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'collection_link',
            'text',
            [
                'name' => 'collection_link',
                'label' => __('Collection Link'),
                'title' => __('Collection Link'),
                'required' => false
            ]
        );

        $this->_eventManager->dispatch('altima_lookbookslider_slide_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();

    }

    public function getTabLabel() {
        return __('Products');
    }

    public function getTabTitle() {
        return __('Products');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    protected function _isAllowedAction($resourceId) {
        return $this->_authorization->isAllowed($resourceId);
    }

}