<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;

use Unirgy\DropshipBatch\Model\Batch;

class MassDelete extends AbstractBatch
{
    public function execute()
    {
        $batchIds = $this->getRequest()->getParam('batch');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!is_array($batchIds)) {
            $this->messageManager->addError(__('Please select batch(es)'));
        }
        else {
            try {
                $batch = $this->_hlp->createObj('\Unirgy\DropshipBatch\Model\Batch');
                foreach ($batchIds as $batchId) {
                    $batch->setId($batchId)->delete();
                }
                $this->messageManager->addSuccess(
                    __('Total of %1 record(s) were successfully deleted', count($batchIds))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $resultRedirect->setPath('*/*/index');
    }
}
