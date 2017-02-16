<?php

namespace Hhmedia\Formula\Model;
use Magento\Framework\DataObject\IdentityInterface;


/**
 * Formula Model
 *
 * @method \Hhmedia\Formula\Model\Resource\Page _getResource()
 * @method \Hhmedia\Formula\Model\Resource\Page getResource()
 */
class Formula extends \Magento\Framework\Model\AbstractModel
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
        $this->_init('Hhmedia\Formula\Model\ResourceModel\Formula');
    }

    public function getAvailableStatuses() {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getProducts(\Hhmedia\Formula\Model\Formula $object)
    {
        $tbl = $this->getResource()->getTable(\Hhmedia\Formula\Model\ResourceModel\Formula::TBL_ATT_PRODUCT);
        $select = $this->getResource()->getConnection()->select()->from(
            $tbl,
            ['product_id']
        )
        ->where(
            'formula_id = ?',
            (int)$object->getId()
        );
        return $this->getResource()->getConnection()->fetchCol($select);
    }

}