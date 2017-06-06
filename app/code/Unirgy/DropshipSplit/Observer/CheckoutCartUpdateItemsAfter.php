<?php

namespace Unirgy\DropshipSplit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Unirgy\DropshipSplit\Helper\Data as HelperData;
use Unirgy\Dropship\Helper\Data as DropshipHelperData;
use Zend\Json\Json;

class CheckoutCartUpdateItemsAfter extends AbstractObserver implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_splitHlp;

    /**
     * @var DropshipHelperData
     */
    protected $_hlp;

    public function __construct(
        HelperData $helperData,
        DropshipHelperData $dropshipHelperData)
    {
        $this->_splitHlp = $helperData;
        $this->_hlp = $dropshipHelperData;

    }

    public function execute(Observer $observer)
    {
        if (!$this->_splitHlp->isActive()) {
            return;
        }

        $hl = $this->_hlp;
        $cart = $observer->getEvent()->getCart();
        $info = $observer->getEvent()->getInfo();
        $quote = $cart->getQuote();
        $address = $quote->getShippingAddress();

        if (!empty($info['estimate_method']) && is_array($info['estimate_method'])) {
            $details = $address->getUdropshipShippingDetails();
            $details = $this->_hlp->unserializeArr($details);

            foreach ($info['estimate_method'] as $vId=>$code) {
                $r = $address->getShippingRateByCode($code);
                if (!$r) {
                    continue;
                }
                $customerSelected = false;
                $oldCode = @$details['methods'][$vId]['code'];
                if ($oldCode && $oldCode!=$code) {
                    $customerSelected = true;
                } elseif ($oldCode && $oldCode==$code) {
                    $customerSelected = @$details['methods'][$vId]['customer_selected'];
                }
                $vendor = $hl->getVendor($vId);
                $details['methods'][$vId] = [
                    'code' => $code,
                    'cost' => $r->getCost(),
                    'price' => $r->getPrice(),
                    'cost_excl' => (float)$hl->getShippingPrice($r->getCost(), $vendor, $address, 'base'),
                    'cost_incl' => (float)$hl->getShippingPrice($r->getCost(), $vendor, $address, 'incl'),
                    'price_excl' => (float)$hl->getShippingPrice($r->getPrice(), $vendor, $address, 'base'),
                    'price_incl' => (float)$hl->getShippingPrice($r->getPrice(), $vendor, $address, 'incl'),
                    'cost_tax' => (float)$hl->getShippingPrice($r->getCost(), $vendor, $address, 'tax'),
                    'tax' => (float)$hl->getShippingPrice($r->getPrice(), $vendor, $address, 'tax'),
                    'carrier_title' => $r->getCarrierTitle(),
                    'method_title' => $r->getMethodTitle(),
                    'customer_selected' => $customerSelected,
                ];
            }

            $address->setUdropshipShippingDetails(Json::encode($details));
        }
        if ($address) {
            $address->setShippingMethod('udsplit_total');
        }
    }
}
