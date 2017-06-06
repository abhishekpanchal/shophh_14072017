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
 * @package    Unirgy_DropshipVendorImport
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipVendorImport\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipVendorImport\Helper\Data as DropshipVendorImportHelperData;
use Unirgy\DropshipVendorImport\Helper\ProtectedCode;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Source as ModelSource;
use Unirgy\Dropship\Model\Source\AbstractSource;

class Source extends AbstractSource
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
     * @var ModelSource
     */
    protected $_src;

    /**
     * @var ProtectedCode
     */
    protected $_viHlpPr;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    public function __construct(
        HelperData $helperData, 
        DropshipVendorImportHelperData $dropshipVendorImportHelperData, 
        ModelSource $modelSource, 
        ProtectedCode $helperProtectedCode,
        ScopeConfigInterface $configScopeConfigInterface,
        array $data = []
    )
    {
        $this->_hlp = $helperData;
        $this->_viHlp = $dropshipVendorImportHelperData;
        $this->_src = $modelSource;
        $this->_viHlpPr = $helperProtectedCode;
        $this->_scopeConfig = $configScopeConfigInterface;

        parent::__construct($data);
    }

    public function toOptionHash($selector=false)
    {
        $hlp = $this->_hlp;
        $hlpc = $this->_viHlp;

        switch ($this->getPath()) {

            case 'uvimport/import/template_vendor':
                $options = $this->_src->getVendors(true);
                $selector = false;
                break;
            case 'billing_region_id':
            case 'region_id':
                $options = [
                ];
                break;
            case 'billing_country_id':
            case 'country_id':
                $options = [
                ];
                break;
            case 'carrier_code':
            case 'registration_carriers':
                $options = [];
                break;

        default:
            throw new \Exception(__('Invalid request for source options: '.$this->getPath()));
        }

        if ($hlp->isModuleActive('Unirgy_DropshipMicrosite')) {
            if (in_array($this->getPath(), ['carrier_code','registration_carriers'])) {
                $msSrc = $this->_hlp->getObj('\Unirgy\DropshipMicrosite\Model\Source');
                $options = $msSrc->setPath($this->getPath())->toOptionHash(false);
            }
        }
        if ($hlp->isModuleActive('Unirgy_DropshipMicrositePro')) {
            if (in_array($this->getPath(), ['billing_region_id','region_id','billing_country_id','country_id'])) {
                $mspSrc = $this->_hlp->getObj('\Unirgy\DropshipMicrositePro\Model\Source');
                $options = $mspSrc->setPath($this->getPath())->toOptionHash(false);
            }
        }

        if ($selector) {
            $options = [''=>__('* Please select')] + $options;
        }

        return $options;
    }

    public function getRegistrationMajorFieldsOptions()
    {
        $result = [];
        $majorFields = $this->_getRegistrationFields(true, -1);
        foreach ($majorFields as $fieldCode) {
            if (!($field = $this->_viHlpPr->getRegistrationField($fieldCode))
                && substr($fieldCode, -2) == '[]'
            ) {
                $field = $this->_viHlpPr->getRegistrationField(substr($fieldCode, 0, -2));
            }
            if (!$field) continue;
            if (array_key_exists('values', $field) && is_array($field['values'])) {
                $result[$fieldCode] = [
                    'value' => $fieldCode,
                    'label' => $field['label'],
                    'values' => $field['values']
                ];
            } elseif (array_key_exists('options', $field) && is_array($field['options'])) {
                $_values = [];
                foreach ($field['options'] as $optKey=>$optLabel) {
                    $_values[] = [
                        'value' => $optKey,
                        'label' => $optLabel,
                    ];
                }
                $result[$fieldCode] = [
                    'value' => $fieldCode,
                    'label' => $field['label'],
                    'values' => $_values
                ];
            }
        }
        return $result;
    }

    public function getRegistrationDependencyTypes($justValues=false, $selector=false)
    {
        $res = $this->setPath('registration_dependency_types')->toOptionHash($selector);
        return $justValues ? array_keys($res) : $res;
    }
    public function getRegistrationMajorFields($grouped=false, $selector=false)
    {
        return $this->_getRegistrationFields(true, $grouped, $selector);
    }
    public function getRegistrationAllFields($grouped=false, $selector=false)
    {
        return $this->_getRegistrationFields(false, $grouped, $selector);
    }
    public function getRegistrationFieldsets($justValues=false, $selector=false)
    {
        return $this->_getRegistrationFields(-1, ($justValues ? -1 : false), $selector);
    }
    protected function _getRegistrationFields($major=false, $grouped=false, $selector=false)
    {
        $hlp = $this->_hlp;
        $hlpc = $this->_viHlp;
        $options = [];
        $columnsConfig = $this->_scopeConfig->getValue('udsignup/form/fieldsets', ScopeInterface::SCOPE_STORE);
        if (!is_array($columnsConfig)) {
            $columnsConfig = $this->_hlp->unserialize($columnsConfig);
        }
        if (is_array($columnsConfig)) {
            foreach ($columnsConfig as $fsConfig) {
                if (is_array($fsConfig)) {
                    $fields = [];
                    foreach (['top_columns','bottom_columns','left_columns','right_columns'] as $colKey) {
                        if (isset($fsConfig[$colKey]) && is_array($fsConfig[$colKey])) {
                            foreach ($fsConfig[$colKey] as $fieldCode) {
                                if (!($field = $this->_viHlpPr->getRegistrationField($fieldCode))
                                    && substr($fieldCode, -2) == '[]'
                                ) {
                                    $field = $this->_viHlpPr->getRegistrationField(substr($fieldCode, 0, -2));
                                }
                                if (!$field) continue;
                                if (!$major || array_key_exists('options', $field) || array_key_exists('values', $field)) {
                                    if ($grouped>0) {
                                        $fields[$field['name']] = [
                                            'value' => $field['name'],
                                            'label' => $field['label']
                                        ];
                                    } else {
                                        $fields[$field['name']] = $field['label'];
                                    }
                                }
                            }
                        }
                    }
                    if ($major===-1) {
                        $options[$fsConfig['title']] = $fsConfig['title'];
                    } else {
                        if (!empty($fields)) {
                        if ($grouped>0) {
                            $options[] = [
                                'label' => $fsConfig['title'],
                                'value' => $fields
                            ];
                        } else {
                            $options = array_merge($options, $fields);
                        }}
                    }
                }
            }
        }
        if ($selector) {
            $options = [''=>__('* Please select')] + $options;
        }
        return $grouped===-1 ? array_keys($options) : $options;
    }

    protected $_vendorPreferences = [];
    public function getVendorPreferences($filterVisible=false)
    {
        if (!isset($this->_vendorPreferences[$filterVisible])) {
            $hlp = $this->_hlp;

            $visible = $this->_scopeConfig->getValue('udropship/vendor/visible_preferences', ScopeInterface::SCOPE_STORE);
            $visible = $visible ? explode(',', $visible) : false;

            $fieldsets = [];
            foreach ($this->_hlp->config()->getFieldset() as $code=>$node) {
                $node = (object)$node;
                if (@$node->modules && !$hlp->isModulesActive((string)$node->modules)) {
                    continue;
                }
                $fieldsets[$code] = [
                    'position' => (int)@$node->position,
                    'label' => (string)$node->legend,
                    'value' => [],
                ];
            }
            foreach ($this->_hlp->config()->getField() as $code=>$node) {
                $node = (object)$node;
                if (!@$node->fieldset || empty($fieldsets[(string)@$node->fieldset]) || @$node->disabled) {
                    continue;
                }
                if (@$node->modules && !$hlp->isModulesActive((string)$node->modules)) {
                    continue;
                }
                if ($filterVisible && $visible && !in_array($code, $visible)) {
                    continue;
                }
                $field = [
                    'position' => (int)@$node->position,
                    'label' => (string)$node->label,
                    'value' => $code,
                ];
                $fieldsets[(string)$node->fieldset]['value'][] = $field;
            }
            uasort($fieldsets, [$hlp, 'usortByPosition']);
            foreach ($fieldsets as $k=>$v) {
                if (empty($v['value'])) {
                    continue;
                }
                uasort($v['value'], [$hlp, 'usortByPosition']);
            }
            $this->_vendorPreferences[$filterVisible] = $fieldsets;
        }
        return $this->_vendorPreferences[$filterVisible];
    }

}
