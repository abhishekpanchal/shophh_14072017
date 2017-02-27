<?php

/**
 * Magazine Resource Collection
 */
namespace Hhmedia\Magazine\Model\ResourceModel\Magazine;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hhmedia\Magazine\Model\Magazine', 'Hhmedia\Magazine\Model\ResourceModel\Magazine');
    }
}
