<?php

namespace Hhmedia\Collection\Model;

/**
 * Collection Model
 *
 * @method \Hhmedia\Collection\Model\Resource\Page _getResource()
 * @method \Hhmedia\Collection\Model\Resource\Page getResource()
 */
class Collection extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Hhmedia\Collection\Model\ResourceModel\Collection');
    }

}
