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

namespace Altima\Lookbookslider\Model\ResourceModel;

class Page extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected $_date;

    public function __construct(
    \Magento\Framework\Model\ResourceModel\Db\Context $context,
    \Magento\Framework\Stdlib\DateTime\DateTime $date, $resourcePrefix = null
    ) {
        $this->_date = $date;
        parent::__construct($context, $resourcePrefix);
    }

    protected function _construct() {
        $this->_init('altima_lookbookslider_slider_relatedpage', 'slider_id');
    }

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object) {
        $condition = ['slider_id = ?' => (int) $object->getId()];
        $this->getConnection()->delete($this->getTable('altima_lookbookslider_slider_relatedpage'), $condition);

        return parent::_beforeDelete($object);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object) {
        if (!$this->isValidPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
            __('The post URL key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
            __('The post URL key cannot be made of only numbers.')
            );
        }

        $gmtDate = $this->_date->gmtDate();

        if ($object->isObjectNew() && !$object->getCreationTime()) {
            $object->setCreationTime($gmtDate);
        }

        return parent::_beforeSave($object);
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object) {
        $newIds = $object->getRelatedPageIds();
        if (is_array($newIds)) {
            $oldIds = $this->lookupRelatedPageIds($object->getId());
            $this->_updateLinks($object, $newIds, $oldIds, 'altima_lookbookslider_slider_relatedpage', 'related_id');
        }

        return parent::_afterSave($object);
    }

    protected function _updateLinks(
    \Magento\Framework\Model\AbstractModel $object, Array $newRelatedIds, Array $oldRelatedIds, $tableName, $field
    ) {
        $table = $this->getTable($tableName);

        $insert = array_diff($newRelatedIds, $oldRelatedIds);
        $delete = array_diff($oldRelatedIds, $newRelatedIds);

        if ($delete) {
            $where = ['slider_id = ?' => (int) $object->getId(), $field . ' IN (?)' => $delete];

            $this->getConnection()->delete($table, $where);
        }

        if ($insert) {
            $data = [];

            foreach ($insert as $storeId) {
                $data[] = ['slider_id' => (int) $object->getId(), $field => (int) $storeId];
            }

            $this->getConnection()->insertMultiple($table, $data);
        }
    }

    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null) {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object) {
        if ($object->getId()) {
            $relatedPages = $this->lookupRelatedPageIds($object->getId());
            $object->setRelatedPageIds($relatedPages);
        }

        return parent::_afterLoad($object);
    }

    protected function isNumericPageIdentifier(\Magento\Framework\Model\AbstractModel $object) {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    protected function isValidPageIdentifier(\Magento\Framework\Model\AbstractModel $object) {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }

    public function lookupRelatedPageIds($sliderId) {
        return $this->_lookupIds($sliderId, 'altima_lookbookslider_slider_relatedpage', 'related_id');
    }

    protected function _lookupIds($sliderId, $tableName, $field) {
        $adapter = $this->getConnection();

        $select = $adapter->select()->from(
                        $this->getTable($tableName), $field
                )->where(
                'slider_id = ?', (int) $sliderId
        );

        return $adapter->fetchCol($select);
    }

}
