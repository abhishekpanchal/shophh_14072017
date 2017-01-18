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

namespace Altima\Lookbookslider\Model\ResourceModel\Slider;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    protected $_storeManager;
    protected $_date;

    public function __construct(
    \Magento\Framework\Data\Collection\EntityFactory $entityFactory, \Psr\Log\LoggerInterface $logger, \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy, \Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Framework\Stdlib\DateTime\DateTime $date, \Magento\Store\Model\StoreManagerInterface $storeManager, $connection = null, \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);

        $this->_date = $date;
        $this->_storeManager = $storeManager;
    }

    protected function _construct() {
        parent::_construct();
        $this->_init('Altima\Lookbookslider\Model\Slider', 'Altima\Lookbookslider\Model\ResourceModel\Slider');
        $this->_map['fields']['slider_id'] = 'main_table.slider_id';
    }


    public function addActiveFilter() {
        return $this
                        ->addFieldToFilter('is_active', 1)
                        ->addFieldToFilter('publish_time', array('lteq' => $this->_date->gmtDate()));
    }

    public function getSelectCountSql() {
        $countSelect = parent::getSelectCountSql();
        $countSelect->reset(\Magento\Framework\DB\Select::GROUP);

        return $countSelect;
    }

    protected function _afterLoad() {
        $items = $this->getColumnValues('slider_id');
        if (count($items)) {
            $connection = $this->getConnection();
            $select = $connection->select()->from(['cps' => $this->getTable('altima_lookbookslider_slider_relatedpage')])
                    ->where('cps.slider_id IN (?)', $items);
            $result = $connection->fetchAll($select);
            if ($result) {
                $categories = [];
                foreach ($result as $item) {
                    $categories[$item['slider_id']][] = $item['related_id'];
                }
                foreach ($this as $item) {
                    $sliderId = $item->getData('slider_id');
                    if (isset($categories[$sliderId])) {
                        $item->setData('pages', $categories[$sliderId]);
                    }
                }
            }
            $select = $connection->select()->from(['cps' => $this->getTable('altima_lookbookslider_slider_relatedcategory')])
                    ->where('cps.slider_id IN (?)', $items);
            $result = $connection->fetchAll($select);

            if ($result) {
                $categories = [];
                foreach ($result as $item) {
                    $categories[$item['slider_id']][] = $item['related_id'];
                }

                foreach ($this as $item) {
                    $sliderId = $item->getData('slider_id');
                    if (isset($categories[$sliderId])) {
                        $item->setData('categories', $categories[$sliderId]);
                    }
                }
            }
        }

        $this->_previewFlag = false;
        return parent::_afterLoad();
    }


}
