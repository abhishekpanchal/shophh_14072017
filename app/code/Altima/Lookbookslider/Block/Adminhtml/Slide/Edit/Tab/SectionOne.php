<?php

namespace Altima\Lookbookslider\Block\Adminhtml\Slide\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

class SectionOne extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface 
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
        $form->setHtmlIdPrefix('slide_section_one_');

        $fieldset = $form->addFieldset('section_one_fieldset', [
            'legend' => __('Section 1'),
            'class'  => 'fieldset-wide'
            ]
        );

        $fieldset->addType('image', '\Altima\Lookbookslider\Block\Adminhtml\Slide\Helper\Image');

        $fieldset->addField(
            'title_one',
            'text',
            [
                'name' => 'title_one',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'description_one',
            'textarea',
            [
                'name' => 'description_one',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'link_one',
            'text',
            [
                'name' => 'link_one',
                'label' => __('Link'),
                'title' => __('Link'),
                'required' => true
            ]
        );

        $fieldset->addField(
	        'image_one',
	        'image',
	        [
	            'name'        => 'image_one',
	            'label'       => __('Image'),
	            'title'       => __('Image'),
	        ]
	    );

        $this->_eventManager->dispatch('altima_lookbookslider_slide_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();

    }

    public function getTabLabel() {
        return __('Section 1');
    }

    public function getTabTitle() {
        return __('Section 1');
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