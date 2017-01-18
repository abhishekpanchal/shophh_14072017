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

use Magento\Framework\View\Result\CategoryFactory;

class Category extends \Magento\Framework\Model\AbstractModel {

    protected $_categoryFactory = null;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Logger\Monolog $monolog
    ) {
        parent::__construct(
                $context,
                $registry
        );
        $this->_categoryFactory = $categoryFactory;
        $this->_storeManager = $storeManager;
        $this->_monolog = $monolog;
    }

    public function toGridArray($categories) {
        $_result = array();
        foreach ($categories as $item) {
            $_category = $this->_categoryFactory->create()->load($item);
            $_result[$_category->getId()] = $_category->getName();
        }
        return $_result;
    }

}
