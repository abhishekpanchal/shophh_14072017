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

namespace Unirgy\DropshipSplit\Block\Adminhtml\Order;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Session\Quote;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Block\Adminhtml\Order\Create\Shipping\Method\Form;
use Magento\Sales\Model\AdminOrder\Create;
use Magento\Tax\Helper\Data as HelperData;
use Unirgy\DropshipSplit\Helper\Data as DropshipSplitHelperData;
use Unirgy\Dropship\Helper\ProtectedCode;
use Zend\Json\Json;

class ShippingMethod
    extends Form
{
    /**
     * @var DropshipSplitHelperData
     */
    protected $_splitHlp;

    /**
     * @var ProtectedCode
     */
    protected $_hlpPr;
    protected $_hlp;

    public function __construct(Context $context, 
        Quote $sessionQuote, 
        Create $orderCreate, 
        PriceCurrencyInterface $priceCurrency, 
        HelperData $taxData, 
        DropshipSplitHelperData $helperData, 
        ProtectedCode $helperProtectedCode,
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        array $data = [])
    {

        $this->_splitHlp = $helperData;
        $this->_hlpPr = $helperProtectedCode;
        $this->_hlp = $udropshipHelper;

        parent::__construct($context, $sessionQuote, $orderCreate, $priceCurrency, $taxData, $data);
    }


    public function getShippingRates()
    {
        if (!$this->_splitHlp->isActive()) {
            return parent::getShippingRates();
        }

        if (empty($this->_rates)) {
            $groups = [];

            // prepare vendor items
            $q = $this->getQuote();
            $qItems = $q->getAllVisibleItems();
            $this->_hlpPr->prepareQuoteItems($qItems);
            foreach ($qItems as $item) {
                if ($item->getIsVirtual()) {
                    continue;
                }
                $groups[$item->getUdropshipVendor()]['items'][] = $item;
                $groups[$item->getUdropshipVendor()]['rates'] = [];
            }

            // prepare vendor rates
            $methods = [];
            $details = $this->getAddress()->getUdropshipShippingDetails();
            if ($details) {
                $details = $this->_hlp->unserialize($details);
                $methods = isset($details['methods']) ? $details['methods'] : [];
            }
            $qRates = $this->getAddress()->getGroupedAllShippingRates();
            foreach ($qRates as $cCode=>$cRates) {
                foreach ($cRates as $rate) {
                    $vId = $rate->getUdropshipVendor();
                    if (!$vId) {
                        continue;
                    }
                    $rate->setIsSelected(!empty($methods[$vId]['code'])
                        && ($methods[$vId]['code']==$rate->getCarrier().'_'.$rate->getMethod()));
                    if (!isset($groups[$vId]['items'])) continue;
                    $groups[$vId]['rates'][$cCode][] = $rate;
                }
            }
            return $this->_rates = $groups;
        }
        return $this->_rates;
    }

    public function _beforeToHtml()
    {
        parent::_beforeToHtml();
        if ($this->_splitHlp->isActive()) {
            $this->setTemplate('Unirgy_DropshipSplit::udsplit/order_create_shipping.phtml');
        }
    }
}