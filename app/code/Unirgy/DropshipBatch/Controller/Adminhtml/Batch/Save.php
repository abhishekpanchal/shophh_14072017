<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;

class Save extends AbstractBatch
{
    public function execute()
    {
        if ( $this->getRequest()->getPost() ) {
            $r = $this->getRequest();
            $hlp = $this->_bHlp;
            try {
                $hlp->isAllVendorsImport(!$r->getParam('vendor_id'));
                $hlp->useCustomTemplate($r->getParam('use_custom_template'));
                $hlp->processPost();
                $hlp->isAllVendorsImport(false);
                $hlp->useCustomTemplate('');
                $this->messageManager->addSuccess(__('Batch was successfully saved'));

                return $this->_redirect('*/*/');
            } catch (\Exception $e) {
                $hlp->isAllVendorsImport(false);
                $hlp->useCustomTemplate('');
                $this->messageManager->addError($e->getMessage());
                return $this->_redirect('*/*/');
            }
        }
        return $this->_redirect('*/*/');
    }
}
