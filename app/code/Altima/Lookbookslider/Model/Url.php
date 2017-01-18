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

namespace Altima\Lookbookslider\Model;

class Url {

    const PERMALINK_TYPE_DEFAULT = 'default';
    const PERMALINK_TYPE_SHORT = 'short';
    const CONTROLLER_POST = 'slider';
    const CONTROLLER_CATEGORY = 'category';
    const CONTROLLER_ARCHIVE = 'archive';
    const CONTROLLER_SEARCH = 'search';

    protected $_registry;
    protected $_url;
    protected $_scopeConfig;

    public function __construct(
    \Magento\Framework\Registry $registry,
            \Magento\Framework\UrlInterface $url,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->_registry = $registry;
        $this->_url = $url;
        $this->_scopeConfig = $scopeConfig;
    }

    public function getPermalinkType() {
        return $this->_getConfig('type');
    }

    public function getRoute($controllerName = null, $skip = true) {
        if ($controllerName) {
            $controllerName .= '_';
        }

        if ($route = $this->_getConfig($controllerName . 'route')) {
            return $route;
        } else {
            return $skip ? $controllerName : null;
        }
    }

    public function getControllerName($route, $skip = true) {
        foreach ([
    self::CONTROLLER_POST,
    self::CONTROLLER_CATEGORY,
    self::CONTROLLER_ARCHIVE,
    self::CONTROLLER_SEARCH
        ] as $controllerName) {
            if ($this->getRoute($controllerName) == $route) {
                return $controllerName;
            }
        }

        return $skip ? $route : null;
    }

    public function getBaseUrl() {
        return $this->_url->getUrl($this->getRoute());
    }

    public function getUrl($identifier, $controllerName) {
        return $this->_url->getUrl(
                        $this->getUrlPath($identifier, $controllerName)
        );
    }

    public function getUrlPath($identifier, $controllerName) {
        if (is_object($identifier)) {
            $identifier = $identifier->getIdentifier();
        }

        switch ($this->getPermalinkType()) {
            case self::PERMALINK_TYPE_DEFAULT :
                return $this->getRoute() . '/' . $this->getRoute($controllerName) . '/' . $identifier;
            case self::PERMALINK_TYPE_SHORT :
                if ($controllerName == self::CONTROLLER_SEARCH) {
                    return $this->getRoute() . '/' . $this->getRoute($controllerName) . '/' . $identifier;
                } else {
                    return $this->getRoute() . '/' . $identifier;
                }
        }
    }

    protected function _getConfig($key) {
        return $this->_scopeConfig->getValue(
                        'mflookbookslider/permalink/' . $key, \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

}
