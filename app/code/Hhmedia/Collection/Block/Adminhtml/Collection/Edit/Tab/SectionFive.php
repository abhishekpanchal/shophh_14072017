<?php
namespace Hhmedia\Collection\Block\Adminhtml\Collection\Edit\Tab;

class SectionFive extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
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
        $model = $this->_coreRegistry->registry('collection');

        if ($this->_isAllowedAction('Hhmedia_Collection::save')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }

        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('collection_section_five_');

        $fieldset = $form->addFieldset(
            'section_five_fieldset',
            ['legend' => __('Section 5'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
            'title_5',
            'text',
            [
                'name' => 'title_5',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => false,
                'disabled' => $isElementDisabled,
            ]
        );

        $fieldset->addField(
            'link_5',
            'text',
            [
                'name' => 'link_5',
                'label' => __('Link'),
                'title' => __('Link'),
                'required' => false,
                'disabled' => $isElementDisabled,
            ]
        );

        $fieldset->addField(
            'sku_one_5',
            'text',
            [
                'name' => 'sku_one_5',
                'label' => __('Sku 1'),
                'title' => __('Sku 1'),
                'required' => false,
                'disabled' => $isElementDisabled,
            ]
        );

        $fieldset->addField(
            'sku_two_5',
            'text',
            [
                'name' => 'sku_two_5',
                'label' => __('Sku 2'),
                'title' => __('Sku 2'),
                'required' => false,
                'disabled' => $isElementDisabled,
            ]
        );

        $fieldset->addField(
            'sku_three_5',
            'text',
            [
                'name' => 'sku_three_5',
                'label' => __('Sku 3'),
                'title' => __('Sku 3'),
                'required' => false,
                'disabled' => $isElementDisabled,
            ]
        );

        $fieldset->addField(
            'sku_four_5',
            'text',
            [
                'name' => 'sku_four_5',
                'label' => __('Sku 4'),
                'title' => __('Sku 4'),
                'required' => false,
                'disabled' => $isElementDisabled,
            ]
        );

        $this->_eventManager->dispatch('adminhtml_collection_edit_tab_content_prepare_form', ['form' => $form]);
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel()
    {
        return __('Section 5');
    }

    public function getTabTitle()
    {
        return __('Section 5');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}