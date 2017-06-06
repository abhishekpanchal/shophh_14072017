<?php

namespace Unirgy\DropshipSplit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\Layout;
use Unirgy\DropshipSplit\Helper\Data as HelperData;

class ControllerActionLayoutRenderBeforeAdminhtmlSalesOrderCreateIndex extends AbstractObserver implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Layout
     */
    protected $_viewLayout;

    public function __construct(HelperData $helperData, 
        Layout $viewLayout)
    {
        $this->_helperData = $helperData;
        $this->_viewLayout = $viewLayout;

    }

    public function execute(Observer $observer)
    {
        if (!$this->_helperData->isActive()) {
            return;
        }

        $layout = $this->_viewLayout;
        $layout->getBlock('shipping_method')->getChildBlock('form')
            ->setTemplate('Unirgy_DropshipSplit::udsplit/order_create_shipping.phtml');
    }
}
