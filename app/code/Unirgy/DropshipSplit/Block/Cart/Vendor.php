<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipSplit\Block\Cart;

use Magento\Checkout\Helper\Data as CheckoutHelperData;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Tax\Helper\Data as HelperData;

class Vendor extends Template
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var CheckoutHelperData
     */
    protected $_checkoutHelperData;

    protected $priceConverter;

    public function __construct(
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceConverter,
        HelperData $helperData,
        CheckoutHelperData $checkoutHelperData, 
        Context $context,
        array $data = [])
    {
        $this->priceConverter = $priceConverter;
        $this->_helperData = $helperData;
        $this->_checkoutHelperData = $checkoutHelperData;

        parent::__construct($context, $data);
    }

    protected function _construct()
    {
        $this->setTemplate('Unirgy_DropshipSplit::unirgy/dsplit/cart/vendor.phtml');
    }

    public function getSubtotal()
    {
        $subtotal = 0;
        foreach ($this->getItems() as $item) {
            if ($this->_helperData->displayCartPriceInclTax() || $this->_helperData->displayCartBothPrices()) {
                $subtotal += $this->_checkoutHelperData->getSubtotalInclTax($item);
            } else {
                $subtotal += $item->getRowTotal();
            }
        }
        return $subtotal;
    }

    public function getWeight()
    {
        $weight = 0;
        foreach ($this->getItems() as $item) {
            $weight += $item->getFullRowWeight();
        }
        return $weight;
    }

    public function getCarrierName($carrierCode)
    {
        if ($name = $this->_scopeConfig->getValue('carriers/'.$carrierCode.'/title', ScopeInterface::SCOPE_STORE)) {
            return $name;
        }
        return $carrierCode;
    }

    public function getShippingPrice($price, $flag)
    {
        $address = $this->getQuote1()->getShippingAddress();
        $address->setUdropshipVendor($this->getVendor()->getId());
        return $this->formatPrice($this->_helperData->getShippingPrice($price, $flag, $address));
    }

    public function formatPrice($price)
    {
        return $this->priceConverter->convertAndFormat($price, false);
    }

    public function isVirtual()
    {
        $vItems = $this->getItems();
        $isVirtual = true;
        $countItems = 0;
        if (!empty($vItems)) {
            foreach ($vItems as $_item) {
                /* @var $_item \Magento\Quote\Model\Quote\Item */
                if ($_item->isDeleted() || $_item->getParentItemId()) {
                    continue;
                }
                $countItems ++;
                if (!$_item->getIsVirtual()) {
                    $isVirtual = false;
                }
            }
        }
        return $countItems == 0 ? false : $isVirtual;
    }
}