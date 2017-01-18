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

use Magento\Store\Model\System\Store;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;

class Pages extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface {

    protected $_objectFactory;
    protected $_page;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Framework\DataObjectFactory $objectFactory, \Altima\Lookbookslider\Model\Page $page, array $data = []
    ) {
        $this->_objectFactory = $objectFactory;
        $this->_page          = $page;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm() {
        $model             = $this->_coreRegistry->registry('current_model');
        $isElementDisabled = !$this->_isAllowedAction('Altima_Lookbookslider::slider');
        $form              = $this->_formFactory->create();
        $form->setHtmlIdPrefix('slider_');

        $fieldset = $form->addFieldset(
                'page_fieldset', ['legend' => __('CMS Pages:'), 'class' => 'fieldset-wide']
        );

        $fieldset->addField(
                'pages', 'multiselect', [
            'name'     => 'slider[pages]',
            'label'    => __('Visible In'),
            'title'    => __('Visible In'),
            'required' => false,
            'disabled' => $isElementDisabled,
            'values'   => $this->_page->toOptionArray(),
            'value'    => ''
                ]
        );

        $this->_eventManager->dispatch('altima_lookbookslider_slider_edit_tab_meta_prepare_form', ['form' => $form]);
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel() {
        return __('Display on CMS Pages');
    }

    public function getTabTitle() {
        return __('Display on CMS Pages');
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
