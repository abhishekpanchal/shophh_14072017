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

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface {

    protected $_systemStore;
    protected $_categoryCollection;

    const FIELD_NAME_SUFFIX = 'slider';

    protected $_fieldFactory;
    protected $_lookbooksliderHelper;

    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Altima\Lookbookslider\Helper\Data $lookbooksliderHelper, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Config\Model\Config\Structure\Element\Dependency\FieldFactory $fieldFactory, \Magento\Store\Model\System\Store $systemStore, \Altima\Lookbookslider\Model\ResourceModel\Slide\Collection $slideCollection, array $data = []
    ) {
        $this->_lookbooksliderHelper = $lookbooksliderHelper;
        $this->_fieldFactory         = $fieldFactory;
        $this->_systemStore          = $systemStore;
        $this->_slideCollection      = $slideCollection;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareLayout() {
        $this->getLayout()->getBlock('page.title')->setPageTitle($this->getPageTitle());
    }

    protected function _prepareForm() {
        $slider            = $this->getSlider();
        $isElementDisabled = true;
        $form              = $this->_formFactory->create();
        $model             = $this->_coreRegistry->registry('current_model');
        $isElementDisabled = !$this->_isAllowedAction('Altima_Lookbookslider::slider');
        $form->setHtmlIdPrefix('slider_');
        $fieldset          = $form->addFieldset('base_fieldset', ['legend' => __('Slider Information')]);

        if ($model->getId()) {
            $fieldset->addField('slider_id', 'hidden', ['name' => 'id']);
        }
        if (!$model->getTime()) {
            $model->setData('time', '5000');
        }
        if (!$model->getTransPeriod()) {
            $model->setData('trans_period', '500');
        }

        $fieldset->addField(
                'title', 'text', [
            'name'     => 'slider[title]',
            'label'    => __('Slider Title'),
            'title'    => __('Slider Title'),
            'required' => true,
            'disabled' => $isElementDisabled
                ]
        );

        $positionImage = [];
        for ($i = 1; $i <= 5; ++$i) {
            $positionImage[] = $this->getViewFileUrl("Altima_Lookbookslider::images/position/lookbookslider-ex{$i}.png");
        }

        $fieldset->addField(
                'position', 'select', [
            'name'     => 'slider[position]',
            'label'    => __('Position'),
            'title'    => __('Position'),
            'values'   => $this->_lookbooksliderHelper->getBlockIdsToOptionsArray(),
            'required' => true,
            'options'  => $model->getAvailablePosition(),
            'disabled' => $isElementDisabled
                ]
        );

        $fieldset->addField(
                'width', 'text', [
            'name'     => 'slider[width]',
            'label'    => __('Slider Width (px)'),
            'title'    => __('Slider Width (px)'),
            'required' => true,
            'class'    => 'required-entry validate-number validate-greater-than-zero',
            'disabled' => $isElementDisabled
                ]
        );
        $fieldset->addField(
                'height', 'text', [
            'name'     => 'slider[height]',
            'label'    => __('Slider Height (px)'),
            'title'    => __('Slider Height (px)'),
            'required' => true,
            'class'    => 'required-entry validate-number validate-greater-than-zero',
            'disabled' => $isElementDisabled
                ]
        );
        $fieldset->addField(
                'effect', 'multiselect', [
            'name'     => 'slider[effect][]',
            'label'    => __('Transition effect'),
            'title'    => __('Transition effect'),
            //'values' => $categories,
            'values'   => $this->_lookbooksliderHelper->getAnimationEffect(),
            'disabled' => $isElementDisabled,
            'style'    => 'width:100%',
            'note'     => __('You can use more than one effect or leave empty to use the random effect.'),
                ]
        );
        $fieldset->addField(
                'navigation', 'select', [
            'name'     => 'slider[navigation]',
            'label'    => __('Show navigation'),
            'title'    => __('Show navigation'),
            'required' => true,
            'values'   => [
                [
                    'value' => 1,
                    'label' => __('Yes'),
                ],
                [
                    'value' => 2,
                    'label' => __('No'),
                ],
            ],
            'disabled' => $isElementDisabled
                ]
        );

        $fieldset->addField(
                'navigation_hover', 'select', [
            'name'     => 'slider[navigation_hover]',
            'label'    => __('Navigation on hover state only'),
            'title'    => __('Navigation on hover state only'),
            'required' => true,
            'values'   => [
                [
                    'value' => 1,
                    'label' => __('Yes'),
                ],
                [
                    'value' => 2,
                    'label' => __('No'),
                ],
            ],
            'disabled' => $isElementDisabled
                ]
        );

        $fieldset->addField(
                'thumbnails', 'select', [
            'name'     => 'slider[thumbnails]',
            'label'    => __('Show thumbnails'),
            'title'    => __('Show thumbnails'),
            'values'   => [
                [
                    'value' => 1,
                    'label' => __('Yes'),
                ],
                [
                    'value' => 2,
                    'label' => __('No'),
                ],
            ],
            'note'     => __('If YES the thumbnails will be visible, if NO will show the pagination'),
            'disabled' => $isElementDisabled
                ]
        );
        $fieldset->addField(
                'no_resample', 'select', [
            'name'     => 'slider[no_resample]',
            'label'    => __('Deny resize images.'),
            'title'    => __('Deny resize images.'),
            'values'   => [
                [
                    'value' => 1,
                    'label' => __('Yes'),
                ],
                [
                    'value' => 2,
                    'label' => __('No'),
                ],
            ],
            'note'     => __('No re-sample for images that exactly match slide size'),
            'disabled' => $isElementDisabled
                ]
        );

        $fieldset->addField(
                'showslidenames', 'select', [
            'name'   => 'slider[showslidenames]',
            'label'  => __('Show Slide Caption.'),
            'title'  => __('Show Slide Caption.'),
            'values' => [
                [
                    'value' => 1,
                    'label' => __('Yes'),
                ],
                [
                    'value' => 2,
                    'label' => __('No'),
                ],
            ],
            'note'   => __('If YES will show slide caption at the slider bottom')
                ]
        );

        $fieldset->addField(
                'time', 'text', [
            'name'     => 'slider[time]',
            'label'    => __('Pause time'),
            'title'    => __('Pause time'),
            'required' => true,
            'class'    => 'required-entry validate-number validate-greater-than-zero',
            'note'     => __('Milliseconds between the end of the sliding effect and the start of the nex one')
                ]
        );
        $fieldset->addField(
                'trans_period', 'text', [
            'name'     => 'slider[trans_period]',
            'label'    => __('Transition duration'),
            'title'    => __('Transition duration'),
            'required' => true,
            'class'    => 'required-entry validate-number validate-greater-than-zero',
            'note'     => __('Length of the sliding effect in milliseconds')
                ]
        );

        $fieldset->addField(
                'is_active', 'select', [
            'label'    => __('Status'),
            'title'    => __('Slider Status'),
            'name'     => 'slider[is_active]',
            'required' => true,
            'options'  => $model->getAvailableStatuses(),
            'disabled' => $isElementDisabled
                ]
        );
        $fieldset->addField(
                'content_before', 'editor', [
            'name'     => 'slider[content_before]',
            'label'    => __('Content before slider'),
            'title'    => __('Content before'),
            'note'     => __('This content will be shown before slider'),
            'wysiwyg'  => true,
            'required' => false,
                ]
        );
        $fieldset->addField(
                'content_after', 'editor', [
            'name'     => 'slider[content_after]',
            'label'    => __('Content after slider'),
            'title'    => __('Content after'),
            'note'     => __('This content will be shown after slider'),
            'wysiwyg'  => true,
            'required' => false,
                ]
        );

        if (!$model->getId()) {
            $model->setData('is_active', $isElementDisabled ? '0' : '1');
        }

        $dateFormat = $this->_localeDate->getDateFormat(
                \IntlDateFormatter::SHORT
        );

        $this->_eventManager->dispatch('altima_lookbookslider_slider_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        //$form->addFieldNameSuffix(self::FIELD_NAME_SUFFIX);
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
        return __('General Information');
    }

    public function getTabTitle() {
        return __('General Information');
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
