<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;

class VendorBatchGrid extends AbstractBatch
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()
            ->getBlock('udbatch.batch.vendorbatchgrid')
            ->setBatchId($this->getRequest()->getParam('id'));
        $this->_view->renderLayout();
    }
}
