<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Batch;

class Edit extends AbstractBatch
{
    public function execute()
    {
        $resultPage = $this->_initAction();
        $id = $this->getRequest()->getParam('id');
        $resultPage->addBreadcrumb(
            $id ? __('View Batch') : __('Create Batch'),
            $id ? __('View Vendor') : __('Create Batch')
        );
        $resultPage->getConfig()->getTitle()->prepend(
            $this->getHeaderText()
        );
        return $resultPage;
    }

    public function getHeaderText()
    {
        $type = $this->getRequest()->getParam('type');
        if ($this->_registry->registry('batch_data') && $this->_registry->registry('batch_data')->getId() ) {
            $title = '';
            switch ($this->_registry->registry('batch_data')->getBatchType()) {
                case 'export_orders':
                    $title = "View Export Orders Batch '%1'";
                    break;
                case 'import_orders':
                    $title = "View Import Orders Batch '%1'";
                    break;
                case 'export_stockpo':
                    $title = "View Export Stock PO Batch '%1'";
                    break;
                case 'import_stockpo':
                    $title = "View Import Stock PO Batch '%1'";
                    break;
                case 'import_inventory':
                    $title = "View Import Inventory Batch '%1'";
                    break;
            }
            return __(
                $title, $this->_hlp->getObj('\Magento\Framework\Escaper')->escapeHtml($this->_registry->registry('batch_data')->getBatchId())
            );
        } else {
            $title = '';
            switch ($type) {
                case 'export_orders':
                    $title = "Export Orders Batch";
                    break;
                case 'import_orders':
                    $title = "Import Orders Batch";
                    break;
                case 'export_stockpo':
                    $title = "Export Stock PO Batch";
                    break;
                case 'import_stockpo':
                    $title = "Import Stock PO Batch";
                    break;
                case 'import_inventory':
                    $title = "Import Inventory Batch";
                    break;
            }
            return __($title);
        }
    }
}
