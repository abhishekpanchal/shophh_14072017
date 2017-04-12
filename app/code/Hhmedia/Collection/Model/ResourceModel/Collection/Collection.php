<?php

/**
 * Collection Resource Collection
 */
namespace Hhmedia\Collection\Model\ResourceModel\Collection;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hhmedia\Collection\Model\Collection', 'Hhmedia\Collection\Model\ResourceModel\Collection');
    }
}
