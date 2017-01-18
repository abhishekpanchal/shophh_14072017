<?php

/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://shop.altima.net.au/tos
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://shop.altima.net.au/tos
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2016 Altima Web Systems (http://altimawebsystems.com/)
 */

namespace Altima\Lookbookslider\Block\Adminhtml\Slider\Edit\Tab;

class Category extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface {

    const FIELD_NAME_SUFFIX = 'slider';

    protected $_fieldFactory;
    protected $_lookbooksliderHelper;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Altima\Lookbookslider\Helper\Data $lookbooksliderHelper, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory, array $data = []
    ) {
        $this->_lookbooksliderHelper = $lookbooksliderHelper;
        $this->_fieldFactory         = $fieldFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    public function getLookbookslider() {
        return $this->_coreRegistry->registry('current_lookbookslider');
    }

    protected function _prepareForm() {
        $slider            = $this->_coreRegistry->registry('current_model');
        $isElementDisabled = true;
        $form              = $this->_formFactory->create();
        $dependenceBlock   = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Form\Element\Dependence'
        );

        $fieldMaps = [];
        $form->setHtmlIdPrefix('slider_');
        $fieldset  = $form->addFieldset('base_fieldset', ['legend' => __('Slider Information')]);

        $fieldMaps['category_ids'] = $fieldset->addField(
                'categories', 'multiselect', [
            'label'  => __('Categories'),
            'name'   => 'categories',
            'values' => $this->_lookbooksliderHelper->getCategoriesArray(),
                ]
        );

        $this->setChild('form_after', $dependenceBlock);

        $defaultData = [
            'width'        => 400,
            'height'       => 200,
            'slider_speed' => 4500,
        ];

        $form->setValues($slider->getData());
        $form->addFieldNameSuffix(self::FIELD_NAME_SUFFIX);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel() {
        return __('Display on Categories');
    }

    public function getTabTitle() {
        return __('Display on Categories');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function getSlider() {
        return $this->_coreRegistry->registry('slider');
    }

}
