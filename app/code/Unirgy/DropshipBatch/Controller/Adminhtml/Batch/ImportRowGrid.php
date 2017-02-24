<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;

class ImportRowGrid extends AbstractBatch
{
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->getLayout()
            ->getBlock('udbatch.batch.importrowgrid')
            ->setBatchId($this->getRequest()->getParam('id'));
        $this->_view->renderLayout();
    }
}
