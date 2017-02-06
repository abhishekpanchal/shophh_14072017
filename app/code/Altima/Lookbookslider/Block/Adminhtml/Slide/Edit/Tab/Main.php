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

namespace Altima\Lookbookslider\Block\Adminhtml\Slide\Edit\Tab;

use Magento\Backend\Block\Widget\Form\Generic as GenericForm;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Config\Model\Config\Source\Yesno as BooleanOptions;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface {

    protected $wysiwygConfig;
    protected $_systemStore;
    protected $_categoryCollection;
    protected $_lookbooksliderHelper;

    public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\Data\FormFactory $formFactory,
            \Magento\Store\Model\System\Store $systemStore,
            \Altima\Lookbookslider\Model\ResourceModel\Slide\Collection $slideCollection,
            \Altima\Lookbookslider\Helper\Data $lookbooksliderHelper,
            array $data = []
    ) {
        $this->_systemStore          = $systemStore;
        $this->_slideCollection      = $slideCollection;
        $this->_lookbooksliderHelper = $lookbooksliderHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm() {
        $model             = $this->_coreRegistry->registry('current_model');
        $isElementDisabled = !$this->_isAllowedAction('Altima_Lookbookslider::slide');
        $form              = $this->_formFactory->create();
        $form->setHtmlIdPrefix('slide_');

        $fieldset = $form->addFieldset('base_fieldset', [
            'legend' => __('Shot Information'),
            'class'  => 'fieldset-wide'
                ]
        );

        $fieldset->addType('image', '\Altima\Lookbookslider\Block\Adminhtml\Slide\Helper\Image');
        $fieldset->addType('image', '\Altima\Lookbookslider\Block\Adminhtml\Slide\Helper\Bgimage');

        if ($model->getId()) {
            $fieldset->addField('slide_id', 'hidden', ['name' => 'id']);
        }

        $fieldset->addField(
            'title', 'text', [
            'name'     => 'slide[title]',
            'label'    => __('Shot Title'),
            'title'    => __('Shot Title'),
            'required' => true,
            'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'slider_id', 'hidden', [
            'name' => 'slide[slider_id]',
                ]
        );
        $fieldset->addField(
            'caption', 'textarea', [
            'label'    => __('Description'),
            'name'     => 'slide[caption]',
            'required' => true,
                ]
        );
        $fieldset->addField('position', 'text', [
            'label'    => __('Sort Order'),
            'required' => false,
            'name'     => 'slide[position]',
                ]
        );

        /*$fieldset->addField('link', 'text', [
            'label'    => __('Link'),
            'required' => false,
            'name'     => 'slide[link]',
                ]
        );*/

        $fieldset->addField('sharetwitter', 'text', [
            'label'    => __('Share Copy (Twitter)'),
            'required' => false,
            'name'     => 'slide[sharetwitter]',
                ]
        );

        $fieldset->addField('shareother', 'text', [
            'label'    => __('Share Copy (Others)'),
            'required' => false,
            'name'     => 'slide[shareother]',
                ]
        );

        $fieldset->addField(
                'is_active', 'select', [
            'label'    => __('Status'),
            'title'    => __('Shot Status'),
            'name'     => 'slide[is_active]',
            'required' => true,
            'options'  => $model->getAvailableStatuses(),
            'disabled' => $isElementDisabled
                ]
        );

    $fieldset->addField(
        'bg_image',
        'image',
        [
            'name'        => 'slide[bg_image]',
            'label'       => __('Background Image'),
            'title'       => __('Background Image'),
        ]
    );


        $field_hotspots    = $fieldset->addField(
                'hotspots', 'hidden', [
            'name'  => 'slide[hotspots]',
            'title' => __('Hotspots'),
                ]
        );
        $renderer_hotspots = $this->getLayout()->createBlock('Altima\Lookbookslider\Block\Adminhtml\Slide\Edit\Form\Renderer\Hotspots');
        $field_hotspots->setRenderer($renderer_hotspots);

        $field    = $fieldset->addField(
                'image_path', 'hidden', [
            'name'  => 'slide[image_path]',
            'title' => __('Image Field'),
                ]
        );
        $renderer = $this->getLayout()->createBlock('Altima\Lookbookslider\Block\Adminhtml\Slide\Edit\Form\Renderer\Lookbookimage');
        $field->setRenderer($renderer);


        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $dateFormat = $this->_localeDate->getDateFormat(
                \IntlDateFormatter::SHORT
        );

        $this->_eventManager->dispatch('altima_lookbookslider_slide_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _getSpaces($n) {
        $s = '';
        for ($i = 0; $i < $n; $i++) {
            $s .= '--- ';
        }
        return $s;
    }

    public function getTabLabel() {
        return __('Shot Information');
    }

    public function getTabTitle() {
        return __('Shot Information');
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
