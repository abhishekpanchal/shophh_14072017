<?php
namespace Bluebadger\Multiship\Model\ResourceModel\Carrier\Rate;

/**
 * Class Collection
 * @package Bluebadger\Multiship\Model\ResourceModel\Carrier\Rate
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Define resource model and item
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Bluebadger\Multiship\Model\Carrier\Rate',
            'Bluebadger\Multiship\Model\ResourceModel\Carrier\Rate'
        );
    }
}
