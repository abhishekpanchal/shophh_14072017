<?php

namespace Hhmedia\Override\Controller\Cart;

class Index extends \Magento\Checkout\Controller\Cart\Index
{
    public function execute()
    { 
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $cart = $objectManager->get('\Magento\Checkout\Model\Cart');

        //$totalItems = $cart->getQuote()->getItemsCount();

        $totalQuantity = $cart->getQuote()->getItemsQty();

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Shopping Cart ('.$totalQuantity.')'));

        return $resultPage;
    }
}