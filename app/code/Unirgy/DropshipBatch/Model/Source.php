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
 * @package    Unirgy_DropshipBatch
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

/**
* Currently not in use
*/
namespace Unirgy\DropshipBatch\Model;

use Magento\CatalogInventory\Model\Source\Stock;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Unirgy\DropshipBatch\Helper\Data as DropshipBatchHelperData;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Source as ModelSource;
use Unirgy\Dropship\Model\Source\AbstractSource;
use Unirgy\Dropship\Model\VendorFactory;

class Source extends AbstractSource
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var DropshipBatchHelperData
     */
    protected $_bHlp;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ModelSource
     */
    protected $src;

    /**
     * @var Stock
     */
    protected $_sourceStock;

    /**
     * @var VendorFactory
     */
    protected $_vendorFactory;

    public function __construct(
        HelperData $udropshipHelper,
        DropshipBatchHelperData $batchHelper,
        ScopeConfigInterface $scopeConfig,
        ModelSource $source,
        Stock $sourceStock, 
        VendorFactory $vendorFactory,
        array $data = []
    )
    {
        $this->_hlp = $udropshipHelper;
        $this->_bHlp = $batchHelper;
        $this->scopeConfig = $scopeConfig;
        $this->src = $source;
        $this->_sourceStock = $sourceStock;
        $this->_vendorFactory = $vendorFactory;

        parent::__construct($data);
    }

    const NEW_ASSOCIATION_NO = 0;
    const NEW_ASSOCIATION_YES_MANUAL = 1;
    const NEW_ASSOCIATION_YES = 2;

    const INVIMPORT_VSKU_MULTIPID_FIRST = 0;
    const INVIMPORT_VSKU_MULTIPID_ALL = 1;
    const INVIMPORT_VSKU_MULTIPID_REPORT = 2;

    protected $_batchVendors;

    public function toOptionHash($selector=false)
    {
        $hlp = $this->_hlp;
        $hlpb = $this->_bHlp;

        switch ($this->getPath()) {

        case 'batch_type':
            $options = [
                'export_orders' => __('Export Orders'),
                'import_orders' => __('Import Orders'),
            	'import_inventory' => __('Import Inventory'),
            ];
            if ($this->_hlp->isModuleActive('ustockpo')) {
                $options['export_stockpo'] = __('Export Stock POs');
                $options['import_stockpo'] = __('Import Stock POs');
            }
            break;

        case 'batch_imported_file_action':
            $options = [
                '' => __('No Action'),
                'delete' => __('Delete'),
            	'rename' => __('Rename'),
                'move' => __('Move'),
                'rename_move' => __('Rename+Move'),
            ];
            break;

        case 'batch_export_orders_adapter':
        case 'batch_adapter':
            $options = [];
            foreach ($this->_hlp->config()->getBatchAdapter('export_orders') as $code=>$node) {
                $options[$code] = __((string)$node['label']);
            }
            break;

        case 'batch_import_orders_adapter':
            $options = [];
            foreach ($this->_hlp->config()->getBatchAdapter('import_orders') as $code=>$node) {
                $options[$code] = __((string)$node['label']);
            }
            break;
            
        case 'batch_import_inventory_adapter':
            $options = [];
            foreach ($this->_hlp->config()->getBatchAdapter('import_inventory') as $code=>$node) {
                $options[$code] = __((string)$node['label']);
            }
            break;

        case 'batch_import_inventory_reindex':
            $options = [
                'realtime' => __('Realtime'),
                'full' => __('Full'),
                'manual' => __('Manual'),
            ];
            break;

        case 'batch_status':
            $options = [
                'pending' => __('Pending'),
                'scheduled' => __('Scheduled'),
                'missed' => __('Missed'),
                'processing' => __('Processing'),
                'exporting' => __('Exporting'),
                'importing' => __('Importing'),
                'empty' => __('Empty'),
                'success' => __('Success'),
                'partial' => __('Partial'),
                'error' => __('Error'),
                'canceled' => __('Canceled'),
            ];
            break;

        case 'dist_status':
            $options = [
                'pending' => __('Pending'),
                'processing' => __('Processing'),
                'exporting' => __('Exporting'),
                'importing' => __('Importing'),
                'success' => __('Success'),
                'empty' => __('Empty'),
                'error' => __('Error'),
                'canceled' => __('Canceled'),
            ];
            break;

        case 'po_batch_status':
            $options = [
                '' => __('New'),
                'pending' => __('Pending'),
                'exported' => __('Exported'),
            ];
            break;

        case 'batch_export_inventory_method':
            $options = [
                '' => __('* No export'),
                'manual' => __('Manual only'),
                'auto' => __('Auto Scheduled'),
            ];
            break;
        case 'batch_export_orders_method':
            $options = [
                '' => __('* No export'),
                'manual' => __('Manual only'),
                'auto' => __('Auto Scheduled'),
                'instant' => __('Instant'),
                'status_instant' => __('Instant by status'),
            ];
            break;


        case 'batch_import_inventory_method':
        case 'batch_import_orders_method':
            $options = [
                '' => __('* No import'),
                'manual' => __('Manual only'),
                'auto' => __('Auto Scheduled'),
            ];
            break;

        case 'vendors_export_orders':
            $options = $this->getEnabledVendors('export_orders');
            break;

        case 'vendors_import_orders':
            //$options = $this->getEnabledVendors('import_orders');
            $options = $this->src->getVendors(true);
            $options[0] = __("* All Vendors *");
            break;
            
        case 'vendors_import_inventory':
            $options = $this->getEnabledVendors('import_inventory');
            break;

        case 'export_orders_destination':
            $options = [
                '' => __("Vendor's Default locations"),
                'custom'
            ];
            break;

        case 'use_custom_template':
            $options = [
                '' => __("* Use vendor default"),
            ];
            $importTpls = $this->_bHlp->getManualImportTemplateTitles();
            foreach ($importTpls as $_imtpl) {
                $options[$_imtpl] = $_imtpl;
            }
            break;

        case 'use_custom_export_template':
        case 'udropship/batch/statement_export_template':
            $options = [
                '' => __("* Use vendor default"),
            ];
            $exportTpls = $this->_bHlp->getManualExportTemplateTitles();
            foreach ($exportTpls as $_extpl) {
                $options[$_extpl] = $_extpl;
            }
            break;

        case 'udropship/batch/invimport_allow_new_association':
            $options = [
                1 => __('Yes (manual only)'),
                2 => __('Yes (manual and scheduled)'),
                0 => __('No'),
            ];
            break;

        case 'udropship/batch/invimport_vsku_multipid':
            $options = [
                self::INVIMPORT_VSKU_MULTIPID_FIRST  => __('Update only first product'),
                self::INVIMPORT_VSKU_MULTIPID_ALL    => __('Update all products'),
                self::INVIMPORT_VSKU_MULTIPID_REPORT => __('Skip and report error'),
            ];
            break;

        case 'stock_status':
            $options = [];
            $cissOptions = $this->_sourceStock->toOptionArray();
            foreach ($cissOptions as $_cissOpt) {
                $options[$_cissOpt['value']] = $_cissOpt['label'];
            }
            break;

        default:
            throw new \Exception(__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = [''=>__('* Please select')] + $options;
        }

        return $options;
    }

    public function getEnabledVendors($type)
    {
        if (empty($this->_batchVendors[$type])) {
            $this->_batchVendors[$type] = [];
            $vendors = $this->_vendorFactory->create()->getCollection()
                ->addStatusFilter('A')
                ->setOrder('vendor_name', 'asc');
            $vendors->getSelect()->where("batch_{$type}_method<>''");
            foreach ($vendors as $v) {
                $this->_batchVendors[$type][$v->getId()] = $v->getVendorName();
            }
        }
        return $this->_batchVendors[$type];
    }

}