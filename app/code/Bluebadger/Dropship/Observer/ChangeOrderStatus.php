<?php
namespace Bluebadger\Dropship\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class ChangeOrderStatus
 * @package Bluebadger\Dropship\Observer
 */
class ChangeOrderStatus implements ObserverInterface
{
    /**
     * @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * ChangeOrderStatus constructor.
     * @param \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\CollectionFactory $itemCollectionFactory
     */
    public function __construct(
        \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\CollectionFactory $itemCollectionFactory
    )
    {
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $quoteId = $order->getQuoteId();

        /** @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\Collection $items */
        $items = $this->itemCollectionFactory->create();
        $items->addFieldToFilter('quote_id', $quoteId);

        if ($items->count()) {
            /** @var \Bluebadger\Dropship\Model\Carrier\Tablerate\Quote\Item $item */
            foreach ($items as $item) {
                if ($item->getData('call_for_quote')) {
                    $order->hold()->save();
                    break;
                }
            }
        }
    }
}