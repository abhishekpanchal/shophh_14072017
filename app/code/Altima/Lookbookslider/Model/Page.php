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

use Magento\Framework\View\Result\PageFactory;

class Page extends \Magento\Framework\Model\AbstractModel {

    protected $_pageFactory = null;

    public function __construct(
    \Magento\Framework\Model\Context $context,
            \Magento\Framework\Registry $registry,
            \Magento\Cms\Model\PageFactory $pageFactory,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Magento\Framework\Logger\Monolog $monolog
    ) {
        parent::__construct(
                $context, $registry
        );
        $this->_pageFactory = $pageFactory;
        $this->_storeManager = $storeManager;
        $this->_monolog = $monolog;
    }

    public function toOptionArray() {
        $_page = $this->_pageFactory->create();
        $_collection = $_page->getCollection();
        $_result = array();
        $_result[] = array(
                'value' => '',
                'label' => '---None---'
        );
        foreach ($_collection as $item) {

            $data = array(
                'value' => $item->getData('page_id'),
                'label' => $item->getData('title'));
            $_result[] = $data;
        }
        return $_result;
    }

    public function toGridArray($pages) {
        $_result = array();
        foreach ($pages as $item) {
            $_page = $this->_pageFactory->create()->load($item);
            $_result[$_page->getId()] = $_page->getTitle();
        }
        return $_result;
    }

}
