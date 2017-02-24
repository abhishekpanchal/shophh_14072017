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

namespace Unirgy\DropshipSplit\Model\Quote;

use Magento\Quote\Model\Quote\Address\Rate as AddressRate;
use Magento\Quote\Model\Quote\Address\RateResult\AbstractResult;

class Rate extends AddressRate
{
    public function importShippingRate(AbstractResult $rate)
    {
        parent::importShippingRate($rate);
        $this->setUdropshipVendor($rate->getUdropshipVendor());
        return $this;
    }
}