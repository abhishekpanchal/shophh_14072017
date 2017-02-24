<?php

namespace Unirgy\DropshipSplit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Unirgy\DropshipSplit\Helper\Data as HelperData;
use Zend\Json\Json;

class CheckoutControllerMultishippingShippingPost extends AbstractObserver implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_splitHlp;
    protected $_hlp;

    public function __construct(
        HelperData $helperData,
        \Unirgy\Dropship\Helper\Data $udropshipHelper
    )
    {
        $this->_splitHlp = $helperData;
        $this->_hlp = $udropshipHelper;
    }

    public function execute(Observer $observer)
    {
        if (!$this->_splitHlp->isActive()) {
            return;
        }

        $request = $observer->getEvent()->getRequest();
        $quote = $observer->getEvent()->getQuote();

        $methods = $request->getParam('shipping_method');
        $vMethods = $request->getParam('vendor_shipping_method');
        if (!empty($vMethods) && is_array($vMethods)) {
            foreach ($quote->getAllShippingAddresses() as $address) {
                $aId = $address->getId();
                if (empty($vMethods[$aId])) {
                    continue;
                }
                if (empty($methods[$aId])) {
                    $methods[$aId] = 'udsplit_total';
                }

                $details = $address->getUdropshipShippingDetails();
                $details = $this->_hlp->unserializeArr($details);

                $cost = 0;
                $price = 0;
                foreach ($vMethods[$aId] as $vId=>$code) {
                    $r = $address->getShippingRateByCode($code);
                    if (!$r) {
                        continue;
                    }
                    $details['methods'][$vId] = [
                        'code' => $code,
                        'cost' => $r->getCost(),
                        'price' => $r->getPrice(),
                        'carrier_title' => $r->getCarrierTitle(),
                        'method_title' => $r->getMethodTitle(),
                    ];
                    $cost += $r->getCost();
                    $price += $r->getPrice();
                }
                foreach ($address->getAllShippingRates() as $rate) {
                    if ($rate->getCode()=='udsplit_total') {
                        $rate->setPrice($price)->setCost($cost)->save();
                        break;
                    }
                }
                $address->setUdropshipShippingDetails(Json::encode($details));
                $address->save();
            }
        }
        $request->setParam('shipping_method', $methods);
    }
}
