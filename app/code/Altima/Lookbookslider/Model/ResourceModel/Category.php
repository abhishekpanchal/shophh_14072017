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

class Category extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {

    protected function _construct() {
        $this->_init('altima_lookbookslider_category', 'category_id');
    }

    protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object) {
        $condition = ['related_id = ?' => (int) $object->getId()];

        $this->getConnection()->delete($this->getTable('altima_lookbookslider_slider_relatedcategory'), $condition);

        return parent::_beforeDelete($object);
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object) {
        if (!$this->isValidPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
            __('The category URL key contains capital letters or disallowed symbols.')
            );
        }

        if ($this->isNumericPageIdentifier($object)) {
            throw new \Magento\Framework\Exception\LocalizedException(
            __('The category URL key cannot be made of only numbers.')
            );
        }

        return parent::_beforeSave($object);
    }

    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null) {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }

    protected function isNumericPageIdentifier(\Magento\Framework\Model\AbstractModel $object) {
        return preg_match('/^[0-9]+$/', $object->getData('identifier'));
    }

    protected function isValidPageIdentifier(\Magento\Framework\Model\AbstractModel $object) {
        return preg_match('/^[a-z0-9][a-z0-9_\/-]+(\.[a-z0-9_-]+)?$/', $object->getData('identifier'));
    }

}
