<?php

namespace Unirgy\DropshipVendorImport\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipVendorImport\Helper\Data as HelperData;
use Unirgy\DropshipVendorImport\Helper\ProtectedCode;
use Unirgy\DropshipVendorImport\Model\ResourceModel\Countries;
use Unirgy\DropshipVendorImport\Model\ResourceModel\Regions;
use Unirgy\Dropship\Model\VendorFactory;

class Import
{
    /**
     * @var Countries
     */
    protected $_resCountries;

    /**
     * @var Regions
     */
    protected $_resRegions;

    /**
     * @var HelperData
     */
    protected $_viHlp;

    /**
     * @var ProtectedCode
     */
    protected $_viHlpPr;

    /**
     * @var VendorFactory
     */
    protected $_vendorFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var ManagerInterface
     */
    protected $_messageManager;
    protected $_hlp;

    public function __construct(
        Countries $resourceModelCountries,
        Regions $resourceModelRegions,
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        HelperData $helperData, 
        ProtectedCode $helperProtectedCode, 
        VendorFactory $modelVendorFactory, 
        ScopeConfigInterface $configScopeConfigInterface, 
        ManagerInterface $messageManagerInterface
    )
    {
        $this->_resCountries = $resourceModelCountries;
        $this->_resRegions = $resourceModelRegions;
        $this->_hlp = $udropshipHelper;
        $this->_viHlp = $helperData;
        $this->_viHlpPr = $helperProtectedCode;
        $this->_vendorFactory = $modelVendorFactory;
        $this->_scopeConfig = $configScopeConfigInterface;
        $this->_messageManager = $messageManagerInterface;

    }

    protected $_countries;
    protected $_regions;
    public function getCountries()
    {
        if ($this->_countries===null) {
            $this->_countries = $this->_resCountries->toOptionHash();
        }
        return $this->_countries;
    }
    public function getRegions()
    {
        if ($this->_regions===null) {
            $this->_regions = $this->_resRegions->toOptionHash();
        }
        return $this->_regions;
    }
    public function prepareValue($if, $value)
    {
        $countries = $this->getCountries();
        $regions = $this->getRegions();
        if ($if['id']=='country_id' || $if['id']=='billing_country_id') {
            if (isset($countries[$value])) {
            } elseif (($__k = array_search($value, $countries))) {
                $value = $__k;
            }
        } elseif ($if['id']=='region_id' || $if['id']=='billing_region_id') {
            if (isset($regions[$value])) {
            } elseif (($__k = array_search($value, $regions))) {
                $value = $__k;
            }
        } elseif (in_array($if['type'], ['statement_po_type', 'payout_po_status_type', 'notify_lowstock', 'select', 'radios'])) {
            if (false!==($__fv = $this->_findValue($if, $value))) {
                $value = $__fv;
            }
        } elseif (in_array($if['type'], ['multiselect', 'checkboxes'])) {
            $value = explode(';', $value);
            foreach ($value as &$v) {
                if (false!==($__fv = $this->_findValue($if, $v))) {
                    $v = $__fv;
                }
            }
            unset($v);
        }
        return $value;
    }
    protected function _findValue($if, $v)
    {
        $_v = false;
        if (isset($if['values'])) {
            foreach ($if['values'] as $__v) {
                if (strtolower($__v['label'])==strtolower($v)) {
                    $_v = $__v['value'];
                    break;
                } elseif ((string)$__v['value']==(string)$v) {
                    $_v = $v;
                    break;
                }
            }
        } else {
            if (isset($if['options'][$v])) {
                $_v = $v;
            } elseif (false!==($__k = array_search($v, $if['options']))) {
                $_v = $__k;
            }
        }
        return $_v;
    }
    public function uploadAndImport($config)
    {
        $csvFile = $_FILES["groups"]["tmp_name"]["import"]["fields"]["import"]["value"];

        $uvHlp = $this->_viHlp;
        $uvHlpProt = $this->_viHlpPr;
        $groups = $config->getGroups();

        if (!empty($csvFile)) {

            $countries = $this->_resCountries->toOptionHash();
            $regions = $this->_resRegions->toOptionHash();

            $importFields = @$groups['import']['fields']['import_fields']['value'];
            if (!is_array($importFields)) {
                $importFields = [];
            }
            unset($importFields['$ROW']);
            usort($importFields, [$this, 'sortBySortOrder']);

            $nameFound = $emailFound = false;
            foreach ($importFields as $if) {
                if (@$if['field'] == 'vendor_name') {
                    $nameFound = true;
                }
                if (@$if['field'] == 'email') {
                    $emailFound = true;
                }
            }

            if (!$nameFound) {
                throw new \Exception(__('Vendor name must be included in import fields list '));
            }
            /*if (!$emailFound) {
                throw new \Exception($uvHlp->__('Vendor email must be included in import fields list '));
            }*/

            $csvRes = fopen($csvFile, 'r');

            $_oldFiles = $_FILES;
            $_FILES = [];

            $exceptions = [];

            if (@$groups['import']['fields']['skip_header']['value']) {
                fgetcsv($csvRes);
            }

            $_imageFields = [];
            $idx = 0;
            foreach ($importFields as $if) {
                if (empty($if['field'])) continue;
                if (($_ifDef = $uvHlpProt->getImportField($if['field'])) && $_ifDef['type']=='image') {
                    $_imageFields[$if['field']] = $idx;
                }
                $idx++;
            }

            $curLineNumber = 1;
            while (($csvLine = fgetcsv($csvRes))) {
                //$csvLine = $this->_getCsvValues($csvLine);
                $filtered = array_filter(array_map('trim', $csvLine));
                if (empty($filtered)) continue;
                $idx = 0;
                $preparedData = [];
                foreach ($importFields as $if) {
                    if (empty($if['field'])) continue;
                    if (!$uvHlpProt->getImportField($if['field'])) continue;
                    if (@$csvLine[$idx]==='') {$idx++; continue;}
                    $preparedData[$if['field']] = $this->prepareValue($uvHlpProt->getImportField($if['field']), @$csvLine[$idx]);
                    $idx++;
                }
                if (isset($preparedData['street1']) || isset($preparedData['street2'])) {
                    $preparedData['street'] = @$preparedData['street1']."\n".@$preparedData['street2'];
                }
                if (empty($preparedData['vendor_name'])) {
                    $exceptions[] = __('Row #%1: empty vendor name', $curLineNumber);
                }/* elseif (empty($preparedData['email'])) {
                    $exceptions[] = $uvHlp->__('Row #%1: empty email', $curLineNumber);
                }*/ else {

                    $vendor = $this->_vendorFactory->create()->load($preparedData['vendor_name'], 'vendor_name');
                    $vendor->setSkipUdropshipVendorIndexer(1);
                    if (!$vendor->getId()) {
                        $vendor = $this->_vendorFactory->create()->load(@$groups['import']['fields']['template_vendor']['value']);
                        $carrierCode = !empty($preparedData['carrier_code'])
                            ? $preparedData['carrier_code'] : $vendor->getCarrierCode();
                        $vendor->getShippingMethods();
                        $vendor->unsetData('vendor_name');
                        $vendor->unsetData('confirmation_sent');
                        $vendor->unsetData('url_key');
                        $vendor->unsetData('email');
                        $vendor->addData($preparedData);
                        $vendor->setCarrierCode($carrierCode);
                        $vendor->unsVendorId();
                        $shipping = $vendor->getShippingMethods();
                        $postedShipping = [];
                        foreach ($shipping as $sId=>&$_s) {
                            foreach ($_s as &$s) {
                                if ($s['carrier_code']==$vendor->getCarrierCode()) {
                                    $s['carrier_code'] = null;
                                }
                                unset($s['vendor_shipping_id']);
                                $s['on'] = true;
                                $postedShipping[$s['shipping_id']] = $s;
                            }
                        }
                        unset($_s);
                        unset($s);
                        $vendor->setPostedShipping($postedShipping);
                        $vendor->setShippingMethods($shipping);
                    } else {
                        if ($this->_scopeConfig->isSetFlag('uvimport/import/reinit_shipping_methods', ScopeInterface::SCOPE_STORE)) {
                            $tplVendor = $this->_vendorFactory->create()->load(@$groups['import']['fields']['template_vendor']['value']);
                            $carrierCode = !empty($preparedData['carrier_code'])
                                ? $preparedData['carrier_code'] : $tplVendor->getCarrierCode();
                            $tplVendor->getShippingMethods();
                            $shipping = $tplVendor->getShippingMethods();
                            $postedShipping = [];
                            foreach ($shipping as $sId=>&$_s) {
                                foreach ($_s as &$s) {
                                    if ($s['carrier_code']==$vendor->getCarrierCode()) {
                                        $s['carrier_code'] = null;
                                    }
                                    unset($s['vendor_shipping_id']);
                                    $s['on'] = true;
                                    $postedShipping[$s['shipping_id']] = $s;
                                }
                            }
                            unset($_s);
                            unset($s);
                            $vendor->setPostedShipping($postedShipping);
                            $vendor->setShippingMethods($shipping);
                        }
                        $vendor->addData($preparedData);
                    }
                    try {
                        $vendor->getResource()->beginTransaction();
                        $vendor->save();
                        $resave=false;
                        /** @var \Magento\Framework\App\Filesystem\DirectoryList $dirList */
                        $dirList = $this->_hlp->getObj('\Magento\Framework\App\Filesystem\DirectoryList');
                        $baseDir = $dirList->getPath('media');
                        $vendorDir = 'vendor'.DIRECTORY_SEPARATOR.$vendor->getId();
                        $vendorAbsDir = $baseDir.DIRECTORY_SEPARATOR.$vendorDir;
                        /* @var \Magento\Framework\Filesystem\Directory\Write $dirWrite */
                        $dirWrite = $this->_hlp->createObj('\Magento\Framework\Filesystem\Directory\WriteFactory')->create($baseDir);
                        $dirWrite->create($vendorDir);
                        foreach ($_imageFields as $_imgField=>$_imgFieldIdx) {
                            $_imgFieldValue = $vendor->getData($_imgField);
                            $_imgToDir = $vendorAbsDir;
                            if (false!==$this->_copyImageFile(null, $_imgToDir, $_imgFieldValue)) {
                                $vendor->setData($_imgField, 'vendor/'.$vendor->getId().$_imgFieldValue);
                            } else {
                                $vendor->setData($_imgField, '');
                            }
                            $resave=true;
                        }
                        if ($resave) $vendor->save();
                        $vendor->getResource()->commit();
                    } catch (\Exception $e) {
                        $vendor->getResource()->rollBack();
                        $exceptions[] = __('Row #%1: %2', $curLineNumber, $e->getMessage());
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

    protected function _copyImageFile($fromDir, $toDir, &$filename)
    {
        $ds = '/';

        $remote = preg_match('#^https?:#', $filename);
        if (!$remote) {
            return false;
        }
        $basename = basename($filename);

        $fromDir = rtrim($fromDir, '/\\');
        $toDir = rtrim($toDir, '/\\');
        if ($remote) {
            $slashPos = false;
            $fromFilename = $filename;
            $fromExists = true;
            $fromRemote = true;
            $filename = $basename;
        } else {
            $slashPos = strpos($filename, $ds);
            $fromFilename = $fromDir.$ds.ltrim($filename, $ds);
            $fromExists = is_readable($fromFilename);
            $fromRemote = false;
        }

        $toFilename = $toDir.$ds.ltrim($filename, $ds);
        if ($slashPos===false) {
            $toFilename = rtrim($toDir, $ds).$ds.ltrim($filename, $ds);
        } elseif (($dirname = dirname($filename))) {
            $toDir .= $ds.ltrim($dirname, $ds);
        }
        $toExists = is_readable($toFilename);

        $filename = $ds.ltrim($filename, $ds);

        if (!$fromExists) {
            return false;
        }

        $this->_hlp->createObj('\Magento\Framework\Filesystem\Directory\WriteFactory')->create($toDir);

        if ($fromRemote) {
            if (!($ch = curl_init($fromFilename))) {
                $error = __('Unable to open remote file: %1', $fromFilename);
            } else {
                curl_setopt($ch, CURLOPT_NOBODY, 1);
                curl_setopt($ch, CURLOPT_HEADER, 1);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $headResult = curl_exec($ch);
                if ($headResult === false) {
                    $error = __('Unable to fetch remote file: %1', $fromFilename);
                } elseif ($headResult === false || false !== strpos($headResult, '404 Not Found')) {
                    $error = __('"404 Not Found" response for remote file: %1', $fromFilename);
                } else {
                    if (!($fp = fopen($toFilename, 'w'))) {
                        $error = __('Unable to open local file for writing: %1', $toFilename);
                    } else {
                        curl_setopt($ch, CURLOPT_NOBODY, 0);
                        curl_setopt($ch, CURLOPT_HTTPGET, 1);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
                        curl_setopt($ch, CURLOPT_FILE, $fp);
                        curl_setopt($ch, CURLOPT_HEADER, 0);
                        if (!curl_exec($ch)) {
                            $error = __('Unable to fetch remote file: %1', $fromFilename);
                        }
                    }
                }
            }
            if ($ch) {
                curl_close($ch);
            }
            if (!empty($fp)) {
                fclose($fp);
            }
            if (!empty($error)) {
                return false;
            }
        } else {
            if (!@copy($fromFilename, $toFilename)) {
                return false;
            }
        }

        return true;
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