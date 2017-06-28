<?php
namespace Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Merchant;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Merchant
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model and item
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Bluebadger\Dropship\Model\Carrier\Tablerate\Merchant',
            'Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Merchant'
        );
    }
}
