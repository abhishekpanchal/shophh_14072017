<?php

namespace Unirgy\DropshipVendorPromotions\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Quote\Model\Quote\Address\Item;

class Data extends AbstractHelper
{
    public function getQuoteAddrTotal($address, $totalKey, $vId)
    {
        if ($totalKey == 'base_subtotal') {
            $qiKey = 'base_row_total';
        } elseif ($totalKey == 'weight') {
            $qiKey = 'row_weight';
        } elseif ($totalKey == 'total_qty') {
            $qiKey = 'qty';
        } else {
            return false;
        }
        $total = 0;
        foreach ($address->getAllItems() as $item) {
            if ($item instanceof Item) {
                $quoteItem = $item->getAddress()->getQuote()->getItemById($item->getQuoteItemId());
            }
            else {
                $quoteItem = $item;
            }
            if (!$quoteItem->getParentItem() && $quoteItem->getUdropshipVendor()==$vId) {
                $total = $quoteItem->getDataUsingMethod($qiKey);
            }
        }
        return $total;
    }
}