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

}
