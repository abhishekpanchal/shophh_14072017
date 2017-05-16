<?php

namespace Hhmedia\Notes\Model;

/**
 * Notes Model
 *
 * @method \Hhmedia\Notes\Model\Resource\Page _getResource()
 * @method \Hhmedia\Notes\Model\Resource\Page getResource()
 */
class Notes extends \Magento\Framework\Model\AbstractModel
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
        $this->_init('Hhmedia\Notes\Model\ResourceModel\Notes');
    }

    public function getAvailableStatuses() {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }

}