<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Dist;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\Exception;
use Unirgy\DropshipBatch\Model\Batch\Dist;

class MassStatus extends AbstractDist
{
    public function execute()
    {
        $modelIds = (array)$this->getRequest()->getParam('dist');
        $status     = (string)$this->getRequest()->getParam('status');

        try {
            $model = $this->_hlp->createObj('\Unirgy\DropshipBatch\Model\Batch\Dist');
            foreach ($modelIds as $modelId) {
                $model->setId($modelId)->setStatus($status)->save();
            }
            $this->messageManager->addSuccess(
                __('Total of %1 record(s) were successfully updated', count($modelIds))
            );
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
        catch (\Exception $e) {
            $this->messageManager->addException($e, __('There was an error while updating history(ies) status'));
        }

        return $this->_redirect('*/*/');
    }
}
