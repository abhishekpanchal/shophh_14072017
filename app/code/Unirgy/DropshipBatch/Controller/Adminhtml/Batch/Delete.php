<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;

class Delete extends AbstractBatch
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if(($id = $this->getRequest()->getParam('id')) > 0 ) {
            try {
                $model = $this->_hlp->createObj('\Unirgy\DropshipBatch\Model\Batch');
                /* @var $model \Unirgy\DropshipBatch\Model\Batch */
                $model->setId($id)->delete();
                $this->messageManager->addSuccess(__('Batch was successfully deleted'));
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
