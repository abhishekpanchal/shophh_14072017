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

namespace Unirgy\DropshipBatch\Block\Adminhtml;

use Magento\Backend\Block\Widget\Context;
use Magento\Backend\Block\Widget\Grid\Container;
use Unirgy\Dropship\Helper\Data as HelperData;

class Batch extends Container
{
    /**
     * @var HelperData
     */
    protected $_hlp;


    public function __construct(
        Context $context,
        HelperData $helperData,
        array $data = []
    )
    {
        $this->_hlp = $helperData;
        parent::__construct($context, $data);
    }

    public function _construct()
    {

        $this->_blockGroup = 'Unirgy_DropshipBatch';
        $this->_controller = 'adminhtml_batch';
        $this->_headerText = __('Vendor Import/Export Batches');

        if ($this->_hlp->isModuleActive('Unirgy_DropshipStockPo')) {
            $this->addButton('add_export_stockpo', [
                    'label'     => __('Create Stock PO Export Batch'),
                    'class'     => 'add',
                    'onclick'   => "location.href = '{$this->getUrl('*/*/new', ['type'=>'export_stockpo'])}'",
            ], 0);

            $this->addButton('add_import_stockpo', [
                    'label'     => __('Create Stock PO Import Batch'),
                    'class'     => 'add',
                    'onclick'   => "location.href = '{$this->getUrl('*/*/new', ['type'=>'import_stockpo'])}'",
            ], 0);
        }

        $this->addButton('add_export', [
                'label'     => __('Create Order Export Batch'),
                'class'     => 'add',
                'onclick'   => "location.href = '{$this->getUrl('*/*/new', ['type'=>'export_orders'])}'",
        ], 0);

        $this->addButton('add_import', [
                'label'     => __('Create Tracking Import Batch'),
                'class'     => 'add',
                'onclick'   => "location.href = '{$this->getUrl('*/*/new', ['type'=>'import_orders'])}'",
        ], 0);

        $this->addButton('add_invimport', [
                'label'     => __('Create Inventory Import Batch'),
                'class'     => 'add',
                'onclick'   => "location.href = '{$this->getUrl('*/*/new', ['type'=>'import_inventory'])}'",
        ], 0);

        $this->removeButton('add');
    }

}
