<?php

namespace Hhmedia\Magazine\Model;
use Magento\Framework\DataObject\IdentityInterface;
/**
 * Magazine Model
 *
 * @method \Hhmedia\Magazine\Model\Resource\Page _getResource()
 * @method \Hhmedia\Magazine\Model\Resource\Page getResource()
 */
class Magazine extends \Magento\Framework\Model\AbstractModel implements IdentityInterface
{
    /**
     * Initialize resource model
     *
     * @return void
     */

    const CACHE_TAG = 'mg_products_grid';

    protected $_cacheTag = 'mg_products_grid';

    protected $_eventPrefix = 'mg_products_grid';

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected function _construct()
    {
        $this->_init('Hhmedia\Magazine\Model\ResourceModel\Magazine');
    }

    public function getAvailableStatuses() {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getProducts(\Hhmedia\Magazine\Model\Magazine $object)
    {
        $tbl = $this->getResource()->getTable(\Hhmedia\Magazine\Model\ResourceModel\Magazine::TBL_ATT_PRODUCT);
        $select = $this->getResource()->getConnection()->select()->from(
            $tbl,
            ['product_id']
        )
        ->where(
            'magazine_id = ?',
            (int)$object->getId()
        );
        return $this->getResource()->getConnection()->fetchCol($select);
    }

}