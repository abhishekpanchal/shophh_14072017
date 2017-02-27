<?php

namespace Unirgy\DropshipVendorImport\Model;

use Magento\Framework\Message\ManagerInterface;
use Unirgy\DropshipTierShipping\Helper\Data as DropshipTierShippingHelperData;
use Unirgy\DropshipVendorImport\Helper\Data as DropshipVendorImportHelperData;
use Unirgy\DropshipVendorImport\Helper\ProtectedCode;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Source as ModelSource;

class ImportRates
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var DropshipVendorImportHelperData
     */
    protected $_viHlp;

    /**
     * @var ProtectedCode
     */
    protected $_viHlpPr;

    /**
     * @var ModelSource
     */
    protected $_src;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;

    public function __construct(HelperData $helperData, 
        DropshipVendorImportHelperData $dropshipVendorImportHelperData, 
        ProtectedCode $helperProtectedCode, 
        ModelSource $modelSource,
        ManagerInterface $messageManagerInterface)
    {
        $this->_hlp = $helperData;
        $this->_viHlp = $dropshipVendorImportHelperData;
        $this->_viHlpPr = $helperProtectedCode;
        $this->_src = $modelSource;
        $this->_messageManager = $messageManagerInterface;

    }

    public function uploadAndImport($config)
    {
        if (!$this->_hlp->isModuleActive('Unirgy_DropshipTierShipping')) return;
        $csvFile = $_FILES["groups"]["tmp_name"]["import_rates"]["fields"]["import"]["value"];

        $uvHlp = $this->_viHlp;
        $uvHlpProt = $this->_viHlpPr;
        $groups = $config->getGroups();

        if (!empty($csvFile)) {

            $vendors = $this->_src->getVendors(true);

            $importColumns = @$groups['import_rates']['fields']['import_columns']['value'];
            if (!is_array($importColumns)) {
                $importColumns = [];
            }
            unset($importColumns['$ROW']);
            usort($importColumns, [$this, 'sortBySortOrder']);

            $nameFound = false;
            foreach ($importColumns as $if) {
                if (@$if['column'] == 'vendor_name') {
                    $nameFound = true;
                }
            }

            if (!$nameFound) {
                throw new \Exception(__('Vendor name must be included in import columns list '));
            }

            $csvRes = fopen($csvFile, 'r');

            $_oldFiles = $_FILES;
            $_FILES = [];

            $exceptions = [];

            $multiplyQty = trim($groups['import_rates']['fields']['multiply_input']['value']);
            $multiplyQty = max($multiplyQty, 1);

            if (@$groups['import_rates']['fields']['skip_header']['value']) {
                fgetcsv($csvRes);
            }

            $curLineNumber = 1;
            while (($csvLine = fgetcsv($csvRes))) {
                //$csvLine = $this->_getCsvValues($csvLine);
                $filtered = array_filter(array_map('trim', $csvLine));
                if (empty($filtered)) continue;
                $idx = 0;
                $preparedData = [];
                $vendorName = null;
                foreach ($importColumns as $if) {
                    if ($if['column']=='vendor_name') {
                        $vendorName = @$csvLine[$idx];
                        $idx++; continue;
                    }
                    if (@$csvLine[$idx]===''||@$csvLine[$idx]==0) {$idx++; continue;}
                    $dtId = $if['delivery_type_id'];
                    $conditions = [];
                    $__cm = 0;
                    while ($__cm++<$multiplyQty) {
                        $conditions[] = [
                            'condition_to'=>$if['condition_to']*$__cm,
                            'price'=>@$csvLine[$idx]*$__cm,
                        ];
                    }
                    $__pd = [
                        'customer_shipclass_id'=>(array)@$if['customer_shipclass_id'],
                        'condition_name'=>$if['condition_name'],
                        'sort_order'=>@$if['rate_sort_order'],
                        'condition'=>$conditions,
                    ];
                    $preparedData[$dtId][] = $__pd;
                    $idx++;
                }
                if (empty($vendorName)) {
                    $exceptions[] = __('Row #%1: empty vendor name', $curLineNumber);
                } elseif (!($vId = array_search($vendorName, $vendors))) {
                    $exceptions[] = __('Row #%1: vendor not found', $curLineNumber);
                } else {
                    /** @var \Unirgy\DropshipTierShipping\Helper\Data $tsHlp */
                    $tsHlp = $this->_hlp->getObj('\Unirgy\DropshipTierShipping\Helper\Data');
                    foreach ($preparedData as $dtId=>$__pd) {
                        $tsHlp->saveVendorV2SimpleCondRates($vId, [$dtId=>$__pd, 'delivery_type'=>$dtId]);
                    }
                }
                $curLineNumber++;
            }

            $_FILES = $_oldFiles;

            if (!empty($exceptions)) {
                $this->_messageManager->addError("\n" . implode("\n", $exceptions));
                //throw new \Exception( "\n" . implode("\n", $exceptions) );
            }
        }
    }

    private function _getCsvValues($string, $separator=",")
    {
        $elements = explode($separator, trim($string));
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], '"');
            if ($nquotes %2 == 1) {
                for ($j = $i+1; $j < count($elements); $j++) {
                    if (substr_count($elements[$j], '"') > 0) {
                        // Put the quoted string's pieces back together again
                        array_splice($elements, $i, $j-$i+1, implode($separator, array_slice($elements, $i, $j-$i+1)));
                        break;
                    }
                }
            }
            if ($nquotes > 0) {
                // Remove first and last quotes, then merge pairs of quotes
                $qstr =& $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
            $elements[$i] = trim($elements[$i]);
        }
        return $elements;
    }
    public function sortBySortOrder($a, $b)
    {
        if (@$a['sort_order']<@$b['sort_order']) {
            return -1;
        } elseif (@$a['sort_order']>@$b['sort_order']) {
            return 1;
        }
        return 0;
    }
}