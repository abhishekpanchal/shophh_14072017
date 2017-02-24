<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;

class ExportRowGrid extends AbstractBatch
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()
            ->getBlock('udbatch.batch.exportrowgrid')
            ->setBatchId($this->getRequest()->getParam('id'));
        $this->_view->renderLayout();
    }
}
