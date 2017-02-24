<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipBatch\Block\Adminhtml\Batch\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Tabs as WidgetTabs;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;

class Tabs extends WidgetTabs
{
    /**
     * @var Registry
     */
    protected $_registry;

    public function __construct(Context $context,
        EncoderInterface $jsonEncoder, 
        Session $authSession, 
        Registry $frameworkRegistry,
        array $data = [])
    {
        $this->_registry = $frameworkRegistry;

        parent::__construct($context, $jsonEncoder, $authSession, $data);
        $this->setId('batch_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Manage Batches'));
    }

    protected function _beforeToHtml()
    {
        $id = $this->_request->getParam('id', 0);

        if ($id) {
            $batch = $this->_registry->registry('batch_data');
            $this->addTab('form_section', [
                'label'     => __('Batch Information'),
                'title'     => __('Batch Information'),
                'content'   => $this->getLayout()->createBlock('Unirgy\DropshipBatch\Block\Adminhtml\Batch\Edit\Tab\Form')
                    ->setVendorId($id)
                    ->toHtml(),
            ]);

            $export = in_array($batch->getBatchType(), ['export_orders', 'export_stockpo', 'export_inventory']);
            $this->addTab('dist_section', [
                'label'     => __($export ? 'Destinations' : 'Sources'),
                'title'     => __($export ? 'Destinations' : 'Sources'),
                'content'   => $this->getLayout()->createBlock('\Unirgy\DropshipBatch\Block\Adminhtml\Batch\Edit\Tab\Dist', 'udbatch.dist.grid')
                    ->setVendorId($id)
                    ->toHtml(),
            ]);

            if ($export) {
                $block = $this->getLayout()->createBlock('\Unirgy\DropshipBatch\Block\Adminhtml\Batch\Edit\Tab\Export\Rows', 'udbatch.rows.grid');
            } else {
                $block = $this->getLayout()->createBlock('\Unirgy\DropshipBatch\Block\Adminhtml\Batch\Edit\Tab\Import\Rows', 'udbatch.rows.grid');
            }
            $this->addTab('rows_section', [
                'label'     => __('Data Rows'),
                'title'     => __('Data Rows'),
                'content'   => $block->setVendorId($id)->toHtml(),
            ]);
        } else {
            if ($this->getRequest()->getParam('type')=='export_orders') {
                $this->addTab('export_section', [
                    'label'     => __('Export Orders'),
                    'title'     => __('Export Orders'),
                    'content'   => $this->getLayout()->createBlock('Unirgy\DropshipBatch\Block\Adminhtml\Batch\Edit\Tab\Export')->toHtml(),
                ]);
            } elseif ($this->getRequest()->getParam('type')=='import_orders') {
                $this->addTab('import_section', [
                    'label'     => __('Import Orders'),
                    'title'     => __('Import Orders'),
                    'content'   => $this->getLayout()->createBlock('Unirgy\DropshipBatch\Block\Adminhtml\Batch\Edit\Tab\Import')->toHtml(),
                ]);
            } elseif ($this->getRequest()->getParam('type')=='export_stockpo') {
                $this->addTab('export_section', [
                    'label'     => __('Export Stock PO'),
                    'title'     => __('Export Stock PO'),
                    'content'   => $this->getLayout()->createBlock('Unirgy\DropshipBatch\Block\Adminhtml\Batch\Edit\Tab\Export')->toHtml(),
                ]);
            } elseif ($this->getRequest()->getParam('type')=='import_stockpo') {
                $this->addTab('import_section', [
                    'label'     => __('Import Stock PO'),
                    'title'     => __('Import Stock PO'),
                    'content'   => $this->getLayout()->createBlock('Unirgy\DropshipBatch\Block\Adminhtml\Batch\Edit\Tab\Import')->toHtml(),
                ]);
            } elseif ($this->getRequest()->getParam('type')=='import_inventory') {
                $this->addTab('import_section', [
                    'label'     => __('Import Inventory'),
                    'title'     => __('Import Inventory'),
                    'content'   => $this->getLayout()->createBlock('Unirgy\DropshipBatch\Block\Adminhtml\Batch\Edit\Tab\Invimport')->toHtml(),
                ]);
            }
        }

        return parent::_beforeToHtml();
    }
}