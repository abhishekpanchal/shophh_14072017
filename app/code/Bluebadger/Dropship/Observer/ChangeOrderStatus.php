<?php
namespace Bluebadger\Dropship\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

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
     * @var \Bluebadger\Dropship\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * ChangeOrderStatus constructor.
     * @param \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\CollectionFactory $itemCollectionFactory
     * @param \Bluebadger\Dropship\Logger\Logger $logger
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\CollectionFactory $itemCollectionFactory,
        \Bluebadger\Dropship\Logger\Logger $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    )
    {
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @inheritdoc
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var array $orderIds */
        $orderIds = $observer->getEvent()->getOrderIds();

        if (!is_array($orderIds) || (!array_key_exists(0, $orderIds))) {
            $message = 'No order ID found.';
            $this->logger->error('ChangeOrderStatus: ' . $message);
            throw new LocalizedException(__($message));
        }

        $order = $this->orderRepository->get($orderIds[0]);

        if (!$order->getId()) {
            $message = 'New order found with ID ' . $orderIds[0];
            $this->logger->error('ChangeOrderStatus: ' . $message);
            throw new LocalizedException(__($message));
        }

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