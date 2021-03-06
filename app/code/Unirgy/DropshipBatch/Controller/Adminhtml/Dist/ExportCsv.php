<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Dist;

class ExportCsv extends AbstractDist
{
    public function execute()
    {
        $this->_view->loadLayout();
        $fileName = 'batch_actions.csv';
        $content = $this->_view->getLayout()->getBlock('udbatch_dist_grid');

        return $this->_fileFactory->create(
            $fileName,
            $content->getCsvFile($fileName),
            \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
        );
    }
}
