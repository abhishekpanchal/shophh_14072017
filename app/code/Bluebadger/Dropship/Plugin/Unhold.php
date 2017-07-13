<?php
namespace Bluebadger\Dropship\Plugin;

/**
 * Class Unhold
 * @package Bluebadger\Dropship\Plugin
 */
class Unhold
{
    /**
     * @var \Unirgy\Dropship\Helper\ProtectedCode\OrderSave
     */
    protected $orderSave;

    /**
     * Unhold constructor.
     * @param \Unirgy\Dropship\Helper\ProtectedCode\OrderSave $orderSave
     */
    public function __construct(
        \Unirgy\Dropship\Helper\ProtectedCode\OrderSave $orderSave
    )
    {
        $this->orderSave = $orderSave;
    }

    /**
     * @param \Magento\Sales\Model\Order $subject
     */
    public function afterUnhold(\Magento\Sales\Model\Order $subject, $result)
    {
        $this->orderSave->sales_order_save_after($subject, true);
    }
}