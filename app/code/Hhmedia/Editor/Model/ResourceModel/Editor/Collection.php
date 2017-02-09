<?php

/**
 * Editor Resource Collection
 */
namespace Hhmedia\Editor\Model\ResourceModel\Editor;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hhmedia\Editor\Model\Editor', 'Hhmedia\Editor\Model\ResourceModel\Editor');
    }
}
