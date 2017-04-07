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

class Slide extends \Magento\Framework\Model\AbstractModel {

    const BASE_MEDIA_PATH = 'altima/lookbookslider/images';
    const SLIDE_TARGET_SELF = 0;
    const SLIDE_TARGET_PARENT = 1;
    const SLIDE_TARGET_BLANK = 2;
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
    const HOME_YES = 1;
    const HOME_NO = 0; 
    const STORY_TEMPLATE_YES = 1;
    const STORY_TEMPLATE_NO = 0; 

    protected $_sliderCollectionFactory;
    protected $_storeViewId = null;
    protected $_slideFactory;
    protected $_valueFactory;
    protected $_valueCollectionFactory;
    protected $_formFieldHtmlIdPrefix = 'page_';
    protected $_storeManager;
    protected $_monolog;
    protected $_eventPrefix = 'altima_lookbookslider_slide';
    protected $_eventObject = 'lookbookslider_slide';
    protected $_url;

    public function __construct(
    \Magento\Framework\Model\Context $context,
    \Magento\Framework\Registry $registry,
    \Altima\Lookbookslider\Model\ResourceModel\Slide $resource,
    \Altima\Lookbookslider\Model\ResourceModel\Slide\Collection $resourceCollection, 
    \Altima\Lookbookslider\Model\SlideFactory $slideFactory,
    \Altima\Lookbookslider\Model\ResourceModel\Slider\CollectionFactory $sliderCollectionFactory,
    \Magento\Store\Model\StoreManagerInterface $storeManager,
    \Magento\Framework\Logger\Monolog $monolog,
    Cache $cache
    ) {
        parent::__construct(
                $context, $registry, $resource, $resourceCollection
        );
        $this->_slideFactory = $slideFactory;
        $this->_storeManager = $storeManager;
        $this->_sliderCollectionFactory = $sliderCollectionFactory;
        $this->_monolog = $monolog;
        $this->cache = $cache;
        if ($storeViewId = $this->_storeManager->getStore()->getId()) {
            $this->_storeViewId = $storeViewId;
        }
    }

    public function cleanMageCache() {
        return $this->cache->clean(\Zend_Cache::CLEANING_MODE_ALL, array('FPC'));
    }

    public function getFormFieldHtmlIdPrefix() {
        return $this->_formFieldHtmlIdPrefix;
    }

    public function getAvailableSlides() {
        $option[] = [
            'value' => '',
            'label' => __('-------- Please select a slider --------'),
        ];

        $sliderCollection = $this->_sliderCollectionFactory->create();
        foreach ($sliderCollection as $slider) {
            $option[] = [
                'value' => $slider->getId(),
                'label' => $slider->getTitle(),
            ];
        }

        return $option;
    }

    public function getStoreAttributes() {
        return array(
            'name',
            'status',
            'click_url',
            'target',
            'image_alt',
            'image',
        );
    }

    public function getStoreViewId() {
        return $this->_storeViewId;
    }

    public function setStoreViewId($storeViewId) {
        $this->_storeViewId = $storeViewId;

        return $this;
    }

    public function beforeSave() {
        if ($this->getStoreViewId()) {
            $defaultStore = $this->_slideFactory->create()->setStoreViewId(null)->load($this->getId());
            $storeAttributes = $this->getStoreAttributes();
            $data = $this->getData();
            foreach ($storeAttributes as $attribute) {
                if (isset($data['use_default']) && isset($data['use_default'][$attribute])) {
                    $this->setData($attribute . '_in_store', false);
                } else {
                    $this->setData($attribute . '_in_store', true);
                    $this->setData($attribute . '_value', $this->getData($attribute));
                }
                $this->setData($attribute, $defaultStore->getData($attribute));
            }
        }

        return parent::beforeSave();
    }

    public function afterSave() {
        if ($storeViewId = $this->getStoreViewId()) {
            $storeAttributes = $this->getStoreAttributes();

            foreach ($storeAttributes as $attribute) {
                if ($this->getData($attribute . '_in_store')) {
                    try {
                        if ($attribute == 'image' && $this->getData('delete_image')) {
                            $attributeValue->delete();
                        } else {
                            $attributeValue->setValue($this->getData($attribute . '_value'))->save();
                        }
                    } catch (\Exception $e) {
                        $this->_monolog->addError($e->getMessage());
                    }
                } elseif ($attributeValue && $attributeValue->getId()) {
                    try {
                        $attributeValue->delete();
                    } catch (\Exception $e) {
                        $this->_monolog->addError($e->getMessage());
                    }
                }
            }
        }

        return parent::afterSave();
    }

    public function load($id, $field = null) {
        parent::load($id, $field);
        if ($this->getStoreViewId()) {
            $this->getStoreViewValue();
        }

        return $this;
    }

    public function getTargetValue() {
        switch ($this->getTarget()) {
            case self::SLIDE_TARGET_SELF:
                return '_self';
            case self::SLIDE_TARGET_PARENT:
                return '_parent';

            default:
                return '_blank';
        }
    }

    public function getOwnTitle($plural = false) {
        return $plural ? 'Shot' : 'Shots';
    }

    public function isActive() {
        return ($this->getIsActive() == self::STATUS_ENABLED);
    }

    public function getAvailableStatuses() {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }

    public function getParentIds() {
        $k = 'parent_ids';
        if (!$this->hasData($k)) {
            $this->setData($k, $this->getPath() ? explode('/', $this->getPath()) : []
            );
        }

        return $this->getData($k);
    }

    public function getParentId() {
        if ($pIds = $this->getParentIds()) {
            return $pIds[count($pIds) - 1];
        }
        return 0;
    }

    public function getParentSlide($storeFilter = false) {
        $k = 'parent_slide';
        if (!$this->hasData($k)) {

            if ($pId = $this->getParentId()) {
                $slide = clone $this;
                $slide->load($pId);

                if ($slide->getId()) {
                    $this->setData($k, $slide);
                }
            }
        }

        if (!$storeFilter) {
            return $this->getData($k);
        } elseif ($pSlide = $this->getData($k)) {
            if (in_array(0, $this->getStoreId()) || in_array(0, $pSlide->getStoreId()) || array_intersect($this->getStoreId(), $pSlide->getStoreId())
            ) {
                return $pSlide;
            }
        }

        return false;
    }

    public function isParent($slide) {
        if (is_object($slide)) {
            $slide = $slide->getId();
        }

        return in_array($slide, $this->getParentIds());
    }

    public function getChildrenIds() {
        $k = 'children_ids';
        if (!$this->hasData($k)) {

            $categories = \Magento\Framework\App\ObjectManager::getInstance()
                    ->create($this->_collectionName);

            $ids = [];
            foreach ($categories as $slide) {
                if ($slide->isParent($this)) {
                    $ids[] = $slide->getId();
                }
            }

            $this->setData($k, $ids
            );
        }

        return $this->getData($k);
    }

    public function isChild($slide) {
        return $slide->isParent($this);
    }

    public function getLevel() {
        return count($this->getParentIds());
    }

    public function getUrl() {
        return $this->_url->getUrlPath($this, URL::CONTROLLER_CATEGORY);
    }

    public function getSlideUrl() {
        return $this->_url->getUrl($this, URL::CONTROLLER_CATEGORY);
    }

    public function getDisplayHome(){
        return [self::HOME_NO => __('No'), self::HOME_YES => __('Yes')];
    }

    public function getStoryTemplate(){
        return [self::STORY_TEMPLATE_NO => __('No'), self::STORY_TEMPLATE_YES => __('Yes')];
    }

}