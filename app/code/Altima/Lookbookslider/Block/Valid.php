<?php

/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://shop.altima.net.au/tos
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://shop.altima.net.au/tos
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2016 Altima Web Systems (http://altimawebsystems.com/)
 */

namespace Altima\Lookbookslider\Block;

class Valid extends \Magento\Framework\View\Element\Template {

    protected $_coreRegistry;
    protected $_scopeConfig;
    protected $_helper;

    public function __construct(
            \Magento\Framework\View\Element\Template\Context $context,
            \Magento\Framework\Registry $coreRegistry,
            \Altima\Lookbookslider\Helper\Data $helper,
            array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $coreRegistry;
        $this->_scopeConfig  = $context->getScopeConfig();
        $this->_helper       = $helper;
    }

    public function _toHtml() {
        if ($this->_helper->canRun(false)) {
            return '';
        }
        if ($this->_helper->canRun(true)) {
            return base64_decode('PGRpdiBzdHlsZT0iYm9yZGVyOiAxcHggc29saWQgZ3JleTsgcGFkZGluZzogNXB4OyBtYXJnaW4tYm90dG9tOiA1cHg7IG1hcmdpbi10b3A6IDVweDsgdGV4dC1hbGlnbjogY2VudGVyIiA+VGhpcyBMb29rYm9vayBQcm9mZXNzaW9uYWwgZXh0ZW5zaW9uIGlzIHJ1bm5pbmcgb24gYSBkZXZlbG9wbWVudCBzZXJpYWwuIERvIG5vdCB1c2UgdGhpcyBzZXJpYWwgZm9yIHByb2R1Y3Rpb24gZW52aXJvbm1lbnRzLjwvZGl2Pg==');
        }
        return str_replace('[DOMAIN]', $this->_helper->getServerName(), base64_decode('PGRpdiBzdHlsZT0iYm9yZGVyOiAzcHggc29saWQgcmVkOyBwYWRkaW5nOiA1cHg7IG1hcmdpbi1ib3R0b206IDE1cHg7IG1hcmdpbi10b3A6IDE1cHg7Ij5QbGVhc2UgZW50ZXIgYSB2YWxpZCBzZXJpYWwgZm9yIHRoZSBkb21haW4gIltET01BSU5dIiBpbiB5b3VyIGFkbWluaXN0cmF0aW9uIHBhbmVsLiBJZiB5b3UgZG9uJ3QgaGF2ZSBvbmUsIHBsZWFzZSBwdXJjaGFzZSBhIHZhbGlkIGxpY2Vuc2UgZnJvbSA8YSBocmVmPSJodHRwOi8vc2hvcC5hbHRpbWEubmV0LmF1L2FsdGltYS1sb29rYm9vay1wcm9mZXNzaW9uYWwuaHRtbCI+aHR0cDovL3Nob3AuYWx0aW1hLm5ldC5hdS9hbHRpbWEtbG9va2Jvb2stcHJvZmVzc2lvbmFsLmh0bWw8L2E+PGJyPjxicj5JZiB5b3UgaGF2ZSBlbnRlcmVkIGEgdmFsaWQgc2VyaWFsIGFuZCBzdGlsbCBleHBlcmllbmNlIGFueSBwcm9ibGVtIHBsZWFzZSB3cml0ZSB0byA8YSBjbGFzcz0iZW1haWwiIGhyZWY9Im1haWx0bzpzdXBwb3J0QGFsdGltYS5uZXQuYXUiPnN1cHBvcnRAYWx0aW1hLm5ldC5hdTwvYT48L2Rpdj4='));
    }

}
