<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;

class ExportCsv extends AbstractBatch
{
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'batch.csv';
        $content = $this->_view->getLayout()->getBlock('udbatch.batch.grid');

        return $this->_fileFactory->create(
            $fileName,
            $content->getCsvFile($fileName),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
    }
}
