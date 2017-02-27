<?php

namespace Unirgy\DropshipSplit\Model;

use Magento\Checkout\Helper\Data as CheckoutHelper;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Registration as CustomerRegistration;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrlManager;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Locale\CurrencyInterface as CurrencyManager;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;
use Magento\Quote\Api\ShippingMethodManagementInterface as ShippingMethodManager;
use Magento\Catalog\Helper\Product\ConfigurationPool;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\UrlInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class DefaultConfigProvider extends \Magento\Checkout\Model\DefaultConfigProvider
{
    protected $_hlp;
    protected $myCoSession;
    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        CheckoutHelper $checkoutHelper,
        CheckoutSession $checkoutSession,
        CustomerRepository $customerRepository,
        CustomerSession $customerSession,
        CustomerUrlManager $customerUrlManager,
        HttpContext $httpContext,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        QuoteItemRepository $quoteItemRepository,
        ShippingMethodManager $shippingMethodManager,
        ConfigurationPool $configurationPool,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        LocaleFormat $localeFormat,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Customer\Model\Address\Config $addressConfig,
        FormKey $formKey,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\View\ConfigInterface $viewConfig,
        \Magento\Directory\Model\Country\Postcode\ConfigInterface $postCodesConfig,
        \Magento\Checkout\Model\Cart\ImageProvider $imageProvider,
        \Magento\Directory\Helper\Data $directoryHelper,
        CartTotalRepositoryInterface $cartTotalRepository,
        ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shippingMethodConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        UrlInterface $urlBuilder
    ) {
        $this->_hlp = $udropshipHelper;
        $this->myCoSession = $checkoutSession;
        parent::__construct($checkoutHelper, $checkoutSession, $customerRepository, $customerSession, $customerUrlManager, $httpContext, $quoteRepository, $quoteItemRepository, $shippingMethodManager, $configurationPool, $quoteIdMaskFactory, $localeFormat, $addressMapper, $addressConfig, $formKey, $imageHelper, $viewConfig, $postCodesConfig, $imageProvider, $directoryHelper, $cartTotalRepository, $scopeConfig, $shippingMethodConfig, $storeManager, $paymentMethodManagement, $urlBuilder);
    }
    public function getConfig()
    {
        $output = parent::getConfig();
        $quote = $this->myCoSession->getQuote();
        $shippingMethodByVendor = [];
        $udropshipVendors = $udropshipVendorIds = [];
        if ($quote->getId()) {
            $shippingAddress = $quote->getShippingAddress();
            $methods = array();
            $details = $shippingAddress->getUdropshipShippingDetails();
            if ($details) {
                $details = $this->_hlp->unserializeArr($details);
                $methods = isset($details['methods']) ? $details['methods'] : array();
            }
            /*
            foreach ($methods as $vId => $method) {
                $shippingMethodByVendor[$vId] = $method['code'];
            }
            */
            $shippingMethodByVendor = $methods;
            $items = $shippingAddress->getAllItems();
            $hlpPr = $this->_hlp->getObj('\Unirgy\Dropship\Helper\ProtectedCode');
            try {
                $hlpPr->prepareQuoteItems($items);
                $hlpPr->fixQuoteItemsWeight($items);
            } catch (\Exception $e) {
                $this->_hlp->addMessageOnce($e->getMessage());
                return [];
            }
            foreach ($items as $item) {
                $udropshipVendorIds[$item->getUdropshipVendor()] = $item->getUdropshipVendor();
            }
            $udropshipVendorIds = array_values($udropshipVendorIds);
            foreach ($udropshipVendorIds as $vId) {
                $v = $this->_hlp->getVendor($vId);
                $udropshipVendors[$vId] = array(
                    'name' => $v->getVendorName(),
                    'address' => $v->getFormatedAddress('text_small')
                );
            }
        }
        $output['selectedShippingMethodByVendor'] = $shippingMethodByVendor;
        $output['udropshipVendorIds'] = $udropshipVendorIds;
        $output['udropshipVendors'] = $udropshipVendors;
        return $output;
    }
}