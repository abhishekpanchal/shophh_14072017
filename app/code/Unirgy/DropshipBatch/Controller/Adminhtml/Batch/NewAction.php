<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;



class NewAction extends AbstractBatch
{
    public function execute()
    {
        $resultForward = $this->resultForwardFactory->create();
        $resultForward->forward('edit');
        return $resultForward;
    }
}
