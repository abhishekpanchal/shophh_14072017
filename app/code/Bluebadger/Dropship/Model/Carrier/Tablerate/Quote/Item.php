<?php
namespace Bluebadger\Dropship\Model\Carrier\Tablerate\Quote;

use Magento\Framework\Model\AbstractModel;

/**
 * Class Item
 * @package Bluebadger\Dropship\Model\Carrier\Tablerate\Quote
 */
class Item extends AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'bluebadger_dropship_quote_item';
    const KEY_QUOTE_ID = 'quote_item_id';

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var string
     */
    protected $_cacheTag = 'bluebadger_dropship_quote_item';

    /**
     * @var string
     */
    protected $_eventPrefix = 'bluebadger_dropship_quote_item';

    /**
     * @var \Magento\Quote\Model\Quote\Item
     */
    private $quoteItem;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('Bluebadger\Dropship\Model\ResourceModel\Carrier\Tablerate\Quote\Item');
    }

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Quote\Model\Quote\ItemFactory $itemFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    )
    {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->itemFactory = $itemFactory;
    }

    /**
     * @inheritdoc
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Get the related quote item.
     * @return \Magento\Quote\Model\Quote\Item
     */
    public function getCartItem()
    {
        if (!$this->quoteItem) {
            $item = $this->itemFactory->create();
            /** @var \Magento\Quote\Model\Quote\Item quoteItem */
            $this->quoteItem = $item->load($this->getData(self::KEY_QUOTE_ID));
            if ($this->quoteItem->getData('parent_item_id')) {
                $parent = $this->itemFactory->create();
                $parent->load($this->quoteItem->getData('parent_item_id'));
                $this->quoteItem->setQty($parent->getQty());
            }
        }
        return $this->quoteItem;
    }
}