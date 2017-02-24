<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;

class DistGrid extends AbstractBatch
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()
            ->getBlock('udbatch.batch.distgrid')
            ->setBatchId($this->getRequest()->getParam('id'));
        $this->_view->renderLayout();
    }
}
