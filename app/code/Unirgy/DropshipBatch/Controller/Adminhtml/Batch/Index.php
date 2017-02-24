<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;

class Index extends AbstractBatch
{
    public function execute()
    {
        $resultPage = $this->_initAction();
        return $resultPage;
    }
}
