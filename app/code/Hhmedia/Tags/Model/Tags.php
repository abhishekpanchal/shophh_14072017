<?php

namespace Hhmedia\Tags\Model;

/**
 * Tags Model
 *
 * @method \Hhmedia\Tags\Model\Resource\Page _getResource()
 * @method \Hhmedia\Tags\Model\Resource\Page getResource()
 */
class Tags extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    protected function _construct()
    {
        $this->_init('Hhmedia\Tags\Model\ResourceModel\Tags');
    }

    public function getAvailableStatuses() {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }

    public function getTags($productId)
    {
        $tbl = $this->getResource()->getTable(\Hhmedia\Tags\Model\ResourceModel\Tags::TBL_ATT_PRODUCT);
        $select = $this->getResource()->getConnection()->select()->from(
            $tbl,
            ['tags_id']
        )
        ->where(
            'product_id = ?',
            (int)$productId
        );
        return $this->getResource()->getConnection()->fetchCol($select);
    }

    public function getProducts(\Hhmedia\Tags\Model\Tags $object)
    {
        $tbl = $this->getResource()->getTable(\Hhmedia\Tags\Model\ResourceModel\Tags::TBL_ATT_PRODUCT);
        $select = $this->getResource()->getConnection()->select()->from(
            $tbl,
            ['product_id']
        )
        ->where(
            'tags_id = ?',
            (int)$object->getId()
        );
        return $this->getResource()->getConnection()->fetchCol($select);
    }

}