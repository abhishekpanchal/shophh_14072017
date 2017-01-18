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

namespace Altima\Lookbookslider\Block;

use Altima\Lookbookslider\Model\Slider as SliderModel;
use Altima\Lookbookslider\Model\Status;

class Lookbookslider extends \Magento\Framework\View\Element\Template {

    protected $_template = 'Altima_Lookbookslider::lookbookslider.phtml';
    protected $_coreRegistry;
    protected $_sliderCollectionFactory;
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_position = null;
    protected $_isActive = 1;
    protected $_collection;
    protected $_page;

    public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Framework\Registry $coreRegistry,
            \Altima\Lookbookslider\Model\ResourceModel\Slider\CollectionFactory $sliderCollectionFactory,
            \Altima\Lookbookslider\Model\ResourceModel\Slide\CollectionFactory $slideCollectionFactory,
            \Altima\Lookbookslider\Helper\Data $helper,
            \Magento\Cms\Model\Page $page,
            array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry            = $coreRegistry;
        $this->_sliderCollectionFactory = $sliderCollectionFactory;

        $this->_scopeConfig  = $context->getScopeConfig();
        $this->_storeManager = $context->getStoreManager();
        $this->_helper       = $helper;
        $this->_page         = $page;
    }

    protected function _toHtml() {
        $this->_eventManager->dispatch(
                'lookbookslider_layout_render_before', ['block' => $this]
        );
        $store = $this->_storeManager->getStore()->getId();

        if ($this->_scopeConfig->getValue(
                //SliderModel::XML_CONFIG_BANNERSLIDER, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store
                )
        ) {
            return parent::_toHtml();
        }
        return '';
    }

    public function appendChildBlockSlider($slider) {
        $this->append(
                $this->getLayout()->createBlock(
                        'Altima\Lookbookslider\Block\Valid'
                )
        );
        $this->append(
                $this->getLayout()->createBlock(
                        'Altima\Lookbookslider\Block\SliderItem'
                )->setSliderId($slider->getSliderId())
        );
        return $this;
    }

    public function setPosition($position) {
        $sliderCollection = $this->_sliderCollectionFactory
                ->create()
                ->addFieldToFilter('position', $position)
                ->addFieldToFilter('is_active', Status::STATUS_ENABLED);
        $category         = $this->_coreRegistry->registry('current_category');
        if ($category) {
            $categoryPathIds = $category->getPathIds();
            foreach ($sliderCollection as $slider) {
                $sliderCategoryIds = $slider->getCategories();
                if ($sliderCategoryIds) {
                    if (count(array_intersect($categoryPathIds, $sliderCategoryIds)) > 0) {
                        $this->appendChildBlockSlider($slider);
                    }
                }
            }
        }
        if ($this->_page->getId()) {
            $pageIds[] = $this->_page->getId();
            foreach ($sliderCollection as $slider) {
                $sliderPageIds = $slider->getPages();
                if ($sliderPageIds) {
                    $_tmp = count(array_intersect($pageIds, $sliderPageIds));
                    if ($_tmp > 0) {
                        $this->appendChildBlockSlider($slider);
                    }
                }
            }
        }
        return $this;
    }

}
