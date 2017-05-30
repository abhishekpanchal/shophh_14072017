<?php

namespace Altima\Lookbookslider\Block\Adminhtml\Slide\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

class SectionTwo extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface 
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
        $form->setHtmlIdPrefix('slide_section_two_');

        $fieldset = $form->addFieldset('section_two_fieldset', [
            'legend' => __('Section 2'),
            'class'  => 'fieldset-wide'
            ]
        );

        $fieldset->addType('image', '\Altima\Lookbookslider\Block\Adminhtml\Slide\Helper\Image');

        $fieldset->addField(
            'title_two',
            'text',
            [
                'name' => 'title_two',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'description_two',
            'textarea',
            [
                'name' => 'description_two',
                'label' => __('Description'),
                'title' => __('Description'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'link_two',
            'text',
            [
                'name' => 'link_two',
                'label' => __('Link'),
                'title' => __('Link'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'image_two',
            'image',
            [
                'name'        => 'image_two',
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
        return __('Section 2');
    }

    public function getTabTitle() {
        return __('Section 2');
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