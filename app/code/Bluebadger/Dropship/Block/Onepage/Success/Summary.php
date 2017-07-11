<?php
namespace Bluebadger\Dropship\Block\Onepage\Success;

/**
 * Class Summary
 * @package Bluebadger\Dropship\Block
 */
class Summary extends \Magento\Checkout\Block\Onepage\Success
{
    /**
     * @var \Bluebadger\Dropship\Model\Carrier\Tablerate\QuoteItemManagerFactory
     */
    protected $quoteItemManagerFactory;

    /**
     * Summary constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Bluebadger\Dropship\Model\Carrier\Tablerate\QuoteItemManagerFactory $quoteItemManagerFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Bluebadger\Dropship\Model\Carrier\Tablerate\QuoteItemManagerFactory $quoteItemManagerFactory,
        array $data = []
    )
    {
        parent::__construct($context, $checkoutSession, $orderConfig, $httpContext, $data);
        $this->quoteItemManagerFactory = $quoteItemManagerFactory;
    }

    /**
     * Get quote items.
     * @return array
     */
    public function getQuoteItems()
    {
        $quoteItems = [];

        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->_checkoutSession->getLastRealOrder();

        if ($order) {
            /** @var \Bluebadger\Dropship\Model\Carrier\Tablerate\QuoteItemManager $quoteItemManager */
            $quoteItemManager = $this->quoteItemManagerFactory->create();
            $quoteItems = $quoteItemManager->getQuoteItemsSortedByVendor($order->getQuoteId());
        }

        return $quoteItems;
    }
}