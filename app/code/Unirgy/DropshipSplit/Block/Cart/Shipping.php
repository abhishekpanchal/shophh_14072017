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

use Magento\Checkout\Block\Cart\Shipping as CartShipping;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Checkout\Model\Session as ModelSession;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
use Unirgy\DropshipSplit\Helper\Data as HelperData;

class Shipping extends CartShipping
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    public function __construct(Context $context, 
        Session $customerSession, 
        ModelSession $checkoutSession, 
        CompositeConfigProvider $configProvider, 
        HelperData $helperData, 
        array $layoutProcessors = [], 
        array $data = [])
    {
        $this->_helperData = $helperData;

        parent::__construct($context, $customerSession, $checkoutSession, $configProvider, $layoutProcessors, $data);
    }

    public function getEstimateRates()
    {
        if (!$this->_helperData->isActive()) {
            return parent::getEstimateRates();
        }

        if (empty($this->_rates)) {
            $groups = $this->getAddress()->getGroupedAllShippingRates();
            foreach ($groups as $cCode=>$rates) {
                foreach ($rates as $i=>$rate) {
                    if ($rate->getUdropshipVendor() || $rate->getCarrier()=='udsplit' && $rate->getMethod()=='total') {
                        unset($groups[$cCode][$i]);
                    }
                    if (empty($groups[$cCode])) {
                        unset($groups[$cCode]);
                    }
                }
            }
            $this->_rates = $groups;
        }
        return $this->_rates;
    }
}