<?php
namespace Bluebadger\Dropship\Block;

/**
 * Class Summary
 * @package Bluebadger\Dropship\Block
 */
class Summary extends \Magento\Framework\View\Element\Template
{
    const KEY_QUOTE_ID = 'quote_id';
    /**
     * @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * Summary constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\CollectionFactory $itemCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\CollectionFactory $itemCollectionFactory,
        array $data = array()
    )
    {
        parent::__construct($context, $data);
        $this->itemCollectionFactory = $itemCollectionFactory;
    }

    /**
     * @param $quoteId
     * @return \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\Collection
     */
    public function getItems($quoteId)
    {
        /** @var \Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item\Collection $items */
        $items = $this->itemCollectionFactory->create();
        $items->addFieldToFilter(self::KEY_QUOTE_ID, $quoteId);

        return $items;
    }
}