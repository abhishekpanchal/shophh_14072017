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

namespace Altima\Lookbookslider\Model\ResourceModel\Slide;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected $_storeViewId = null;
    protected $_storeManager;
    protected $_addedTable = [];
    protected $_isLoadSliderTitle = FALSE;
    protected $_request;

    protected function _construct() {
        $this->_init('Altima\Lookbookslider\Model\Slide', 'Altima\Lookbookslider\Model\ResourceModel\Slide');
    }

    public function __construct(
    \Magento\Framework\App\Request\Http $request, \Magento\Framework\Data\Collection\EntityFactory $entityFactory, \Psr\Log\LoggerInterface $logger, \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy, \Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Store\Model\StoreManagerInterface $storeManager, $connection = null, \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_storeManager = $storeManager;
        $this->_request = $request;
        if ($storeViewId = $this->_storeManager->getStore()->getId()) {
            $this->_storeViewId = $storeViewId;
        }
    }

    public function setIsLoadSliderTitle($isLoadSliderTitle) {
        $this->_isLoadSliderTitle = $isLoadSliderTitle;

        return $this;
    }

    public function isLoadSliderTitle() {
        return $this->_isLoadSliderTitle;
    }

    protected function _beforeLoad() {
        $slider_id = $this->_request->getParam('slider_id', 0);
        if ($slider_id):
            $this->addFieldToFilter('slider_id', $slider_id);
        endif;
        if ($this->isLoadSliderTitle()) {
            $this->joinSliderTitle();
        }

        return parent::_beforeLoad();
    }

    public function joinSliderTitle() {
        $this->getSelect()->joinLeft(
                ['sliderTable' => $this->getTable('altima_lookbookslider_slider')], 'main_table.slider_id = sliderTable.slider_id', ['title' => 'sliderTable.title', 'slider_is_active' => 'sliderTable.is_active']
        );

        return $this;
    }

    public function getSelectCountSql() {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);

        return $countSelect;
    }

    public function addActiveFilter() {
        return $this->addFieldToFilter('is_active', 1);
    }

    protected function _getGroupedChilds() {
        $childs = [];
        if (count($this)) {
            foreach ($this as $item) {
                $childs[$item->getParentId()][] = $item;
            }
        }
        return $childs;
    }

    public function getTreeOrderedArray() {
        $tree = [];
        if ($childs = $this->_getGroupedChilds()) {
            $this->_toTree(0, $childs, $tree);
        }
        return $tree;
    }

    protected function _toTree($itemId, $childs, &$tree) {
        if ($itemId) {
            $tree[] = $this->getItemById($itemId);
        }

        if (isset($childs[$itemId])) {
            foreach ($childs[$itemId] as $i) {
                $this->_toTree($i->getId(), $childs, $tree);
            }
        }
    }

    public function getStoreViewId() {
        return $this->_storeViewId;
    }

    public function setStoreViewId($storeViewId) {
        $this->_storeViewId = $storeViewId;

        return $this;
    }

}
