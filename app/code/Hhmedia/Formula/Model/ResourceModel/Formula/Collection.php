<?php

/**
 * Formula Resource Collection
 */
namespace Hhmedia\Formula\Model\ResourceModel\Formula;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hhmedia\Formula\Model\Formula', 'Hhmedia\Formula\Model\ResourceModel\Formula');
    }
}
