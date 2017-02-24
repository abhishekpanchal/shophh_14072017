<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Dist;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;

class Grid extends AbstractDist
{
    public function execute()
    {
        $this->_view->loadLayout(false);
        $this->_view->renderLayout();
    }
}
