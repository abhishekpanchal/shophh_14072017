<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Dist;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\Exception;
use Unirgy\DropshipBatch\Helper\Data as HelperData;

class MassRetry extends AbstractDist
{
    public function execute()
    {
        $modelIds = (array)$this->getRequest()->getParam('dist');
        try {
            $this->_bHlp->retryDists($modelIds);
            $this->messageManager->addSuccess(
                __('Total of %1 record(s) were retried', count($modelIds))
            );
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
        catch (\Exception $e) {
            $this->messageManager->addException($e, __('There was an error while retrying distribution: '.$e->getMessage()));
        }

        return $this->_redirect('*/*/');
    }
}
