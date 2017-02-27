<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;



class NewImport extends AbstractBatch
{
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward('edit');
        return $resultForward;
    }
}
