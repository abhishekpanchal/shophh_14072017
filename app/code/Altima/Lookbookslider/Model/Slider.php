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

namespace Altima\Lookbookslider\Model;

use Magento\PageCache\Model\Cache\Type as Cache;

class Slider extends \Magento\Framework\Model\AbstractModel {

    const XML_CONFIG_SLIDESLIDER = 'lookbookslider/general/enable_frontend';
    const STATUS_ENABLED         = 1;
    const STATUS_DISABLED        = 0;
    const SHOW_TITLE_YES         = 1;
    const SHOW_TITLE_NO          = 2;
    const STYLE_CONTENT_YES      = 1;
    const STYLE_CONTENT_NO       = 2;
    const SORT_TYPE_RANDOM       = 1;
    const SORT_TYPE_ORDERLY      = 2;

    /**
     * Evolution slider.
     */
    const STYLESLIDE_EVOLUTION_ONE   = 1;
    const STYLESLIDE_EVOLUTION_TWO   = 2;
    const STYLESLIDE_EVOLUTION_THREE = 3;
    const STYLESLIDE_EVOLUTION_FOUR  = 4;

    /**
     * popup.
     */
    const STYLESLIDE_POPUP = 5;

    /**
     * note slider.
     */
    const STYLESLIDE_SPECIAL_NOTE = 6;

    /**
     * flexslider.
     */
    const STYLESLIDE_FLEXSLIDER_ONE   = 7;
    const STYLESLIDE_FLEXSLIDER_TWO   = 8;
    const STYLESLIDE_FLEXSLIDER_THREE = 9;
    const STYLESLIDE_FLEXSLIDER_FOUR  = 10;

    /**
     * position code of note slider.
     */
    const NOTE_POSITION_TOP_LEFT      = 'top-left';
    const NOTE_POSITION_MIDDLE_TOP    = 'middle-top';
    const NOTE_POSITION_TOP_RIGHT     = 'top-right';
    const NOTE_POSITION_MIDDLE_LEFT   = 'middle-left';
    const NOTE_POSITION_MIDDLE_RIGHT  = 'middle-right';
    const NOTE_POSITION_BOTTOM_LEFT   = 'bottom-left';
    const NOTE_POSITION_MIDDLE_BOTTOM = 'middle-bottom';
    const NOTE_POSITION_BOTTOM_RIGHT  = 'bottom-right';

    protected $_eventPrefix = 'altima_lookbookslider_slider';
    protected $_eventObject = 'lookbookslider_slider';
    protected $_url;
    protected $_categoryCollectionFactory;
    protected $_productCollectionFactory;
    protected $_parentCategories;
    protected $_slideCollectionFactory;
    protected $_cache;

    public function __construct(
            \Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry,
            \Altima\Lookbookslider\Model\ResourceModel\Page\CollectionFactory $categoryCollectionFactory,
            \Altima\Lookbookslider\Model\ResourceModel\Slide\CollectionFactory $slideCollectionFactory,
            \Altima\Lookbookslider\Model\ResourceModel\Slider $resource,
            \Altima\Lookbookslider\Model\ResourceModel\Slider\Collection $resourceCollection,
            Cache $cache
    ) {
        parent::__construct(
                $context, $registry, $resource, $resourceCollection
        );
        $this->_slideCollectionFactory = $slideCollectionFactory;
        $this->cache                   = $cache;
    }

    public function cleanMageCache() {
        return $this->cache->clean(\Zend_Cache::CLEANING_MODE_ALL, array('FPC'));
    }

    protected function _construct() {
        $this->_init('Altima\Lookbookslider\Model\ResourceModel\Page');
    }

    public function getOwnSlideCollection() {
        return $this->_slideCollectionFactory->create()->addFieldToFilter('slider_id', $this->getId());
    }

    public function getOwnTitle($plural = false) {
        return $plural ? 'Slider' : 'Sliders';
    }

    public function isActive() {
        return ($this->getStatus() == self::STATUS_ENABLED);
    }

    public function getAvailableStatuses() {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }

    public function getAvailablePosition() {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }

    public function getUrl() {
        return $this->_url->getUrlPath($this, URL::CONTROLLER_POST);
    }

    public function getSliderUrl() {
        return $this->_url->getUrl($this, URL::CONTROLLER_POST);
    }

    public function getParentCategories() {
        if (is_null($this->_parentCategories)) {
            $this->_parentCategories = $this->_categoryCollectionFactory->create()
                    ->addFieldToFilter('category_id', array('in' => $this->getCategories()))
                    ->addStoreFilter($this->getStoreId())
                    ->addActiveFilter();
        }

        return $this->_parentCategories;
    }

    public function getCategoriesCount() {
        return count($this->getParentCategories());
    }

    public function getRelatedSliders() {
        return $this->getCollection()
                        ->addFieldToFilter('slider_id', array('in' => $this->getRelatedSliderIds() ? : array(0)))
                        ->addStoreFilter($this->getStoreId());
    }

    public function getRelatedPages() {
        return $this->getCollection()
                        ->addFieldToFilter('slider_id', array('in' => $this->getRelatedPageIds() ? : array(0)));
    }

    public function getRelatedProducts() {
        $collection = $this->_productCollectionFactory->create()
                ->addFieldToFilter('entity_id', array('in' => $this->getRelatedProductIds() ? : array(0)));

        if ($storeIds = $this->getStoreId()) {
            $collection->addStoreFilter($storeIds[0]);
        }

        return $collection;
    }

}
