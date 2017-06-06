<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;

class ExportXml extends AbstractBatch
{
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'batch.xml';
        $content = $this->_view->getLayout()->getBlock('udbatch.batch.grid');

        return $this->_fileFactory->create(
            $fileName,
            $content->getExcelFile($fileName),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
    }
}
