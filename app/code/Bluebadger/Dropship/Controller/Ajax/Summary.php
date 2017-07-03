<?php

namespace Bluebadger\Dropship\Controller\Ajax;

/**
 * Class Summary
 * @package Bluebadger\Dropship\Controller\Shipping
 */
class Summary extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $session;

    /** @var \Bluebadger\Dropship\Model\Carrier\Tablerate\Quote\ItemFactory  */
    protected $quoteItemManager;

    /**
     * Summary constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Checkout\Model\Session $session
     * @param \Bluebadger\Dropship\Model\Carrier\Tablerate\QuoteItemManager $quoteItemManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Checkout\Model\Session $session,
        \Bluebadger\Dropship\Model\Carrier\Tablerate\QuoteItemManager $quoteItemManager
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->session = $session;
        $this->quoteItemManager = $quoteItemManager;
    }

    /**
     * Return a list of quote items grouped by
     */
    public function execute()
    {
        $errors = false;
        $quoteItems = [];

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        try {
            $quoteId = $this->session->getQuoteId();
            $quoteItems = $this->quoteItemManager->getQuoteItemsSortedByVendor($quoteId);
        } catch (\Exception $e) {
            $errors = __('An error occurred while retrieving the quote items: ' . $e->getMessage());
        }

        return $result->setData(['errors' => $errors, 'quote' => $quoteItems]);
    }
}