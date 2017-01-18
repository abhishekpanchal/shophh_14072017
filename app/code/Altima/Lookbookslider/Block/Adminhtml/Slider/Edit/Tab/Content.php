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

class Content extends \Magento\Backend\Block\Widget\Form\Generic implements
\Magento\Backend\Block\Widget\Tab\TabInterface {

    protected $_wysiwygConfig;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig, array $data = []
    ) {
        $this->_wysiwygConfig = $wysiwygConfig;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm() {
        $model             = $this->_coreRegistry->registry('current_model');
        $isElementDisabled = !$this->_isAllowedAction('Altima_Lookbookslider::slider');
        $form              = $this->_formFactory->create();
        $form->setHtmlIdPrefix('slider_');
        $fieldset          = $form->addFieldset(
                'content_fieldset', ['legend' => __('Content'), 'class' => 'fieldset-wide']
        );
        $wysiwygConfig     = $this->_wysiwygConfig->getConfig(['tab_id' => $this->getTabId()]);
        $fieldset->addField(
                'content_heading', 'text', [
            'name'     => 'slider[content_heading]',
            'label'    => __('Content Heading'),
            'title'    => __('Content Heading'),
            'disabled' => $isElementDisabled
                ]
        );
        $contentField      = $fieldset->addField(
                'content', 'editor', [
            'name'     => 'slider[content]',
            'style'    => 'height:36em;',
            'required' => true,
            'disabled' => $isElementDisabled,
            'config'   => $wysiwygConfig
                ]
        );
        $renderer          = $this->getLayout()->createBlock(
                        'Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element'
                )->setTemplate(
                'Magento_Cms::page/edit/form/renderer/content.phtml'
        );
        $contentField->setRenderer($renderer);

        $this->_eventManager->dispatch('altima_lookbookslider_slider_edit_tab_content_prepare_form', ['form' => $form]);
        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    public function getTabLabel() {
        return __('Content');
    }

    public function getTabTitle() {
        return __('Content');
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
