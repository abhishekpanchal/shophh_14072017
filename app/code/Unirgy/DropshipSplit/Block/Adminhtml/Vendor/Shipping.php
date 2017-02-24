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
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipSplit\Block\Adminhtml\Vendor;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Config\Model\Config\Source\Email\Template;
use Magento\Directory\Model\Config\Source\CountryFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Source;

class Shipping extends Generic
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    /**
     * @var Source
     */
    protected $_modelSource;

    /**
     * @var Template
     */
    protected $_emailTemplate;

    /**
     * @var CountryFactory
     */
    protected $_sourceCountryFactory;

    /**
     * @var RegionFactory
     */
    protected $_modelRegionFactory;

    public function __construct(Context $context, 
        Registry $registry, 
        FormFactory $formFactory, 
        HelperData $helperData, 
        Source $modelSource, 
        Template $emailTemplate, 
        CountryFactory $sourceCountryFactory, 
        RegionFactory $modelRegionFactory,
        array $data = [])
    {
        $this->_helperData = $helperData;
        $this->_modelSource = $modelSource;
        $this->_emailTemplate = $emailTemplate;
        $this->_sourceCountryFactory = $sourceCountryFactory;
        $this->_modelRegionFactory = $modelRegionFactory;

        parent::__construct($context, $registry, $formFactory, $data);
        $this->setDestElementId('vendor_form');
        //$this->setTemplate('udropship/vendor/form.phtml');
    }

    protected function _prepareForm()
    {
        $vendor = $this->_frameworkRegistry->registry('vendor_data');
        $hlp = $this->_helperData;
        $id = $this->getRequest()->getParam('id');
        $form = new Form();
        $this->setForm($form);

        $fieldset = $form->addFieldset('vendor_form', [
            'legend'=>__('Vendor Info')
        ]);

        $fieldset->addField('reg_id', 'hidden', [
            'name'      => 'reg_id',
        ]);
        $fieldset->addField('password_hash', 'hidden', [
            'name'      => 'password_hash',
        ]);

        $fieldset->addField('vendor_name', 'text', [
            'name'      => 'vendor_name',
            'label'     => __('Vendor Name'),
            'class'     => 'required-entry',
            'required'  => true,
        ]);

        $fieldset->addField('status', 'select', [
            'name'      => 'status1',
            'label'     => __('Status'),
            'class'     => 'required-entry',
            'required'  => true,
            'options'   => $this->_modelSource->setPath('vendor_statuses')->toOptionHash(),
        ]);

        $fieldset->addField('carrier_code', 'select', [
            'name'      => 'carrier_code',
            'label'     => __('Used Carrier'),
            'class'     => 'required-entry',
            'required'  => true,
            'options'   => $this->_modelSource->setPath('carriers')->toOptionHash(true),
        ]);

        $fieldset->addField('email', 'text', [
            'name'      => 'email',
            'label'     => __('Vendor Email'),
            'class'     => 'required-entry validate-email',
            'required'  => true,
            'note'      => __('Email is also used as username'),
        ]);
/*
        $fieldset->addField('password', 'password', array(
            'name'      => 'password',
            'label'     => __('Log In Password'),
            'note'      => __('Login disabled if empty'),
        ));
*/
        $fieldset->addField('telephone', 'text', [
            'name'      => 'telephone',
            'label'     => __('Vendor Telephone'),
            'class'     => 'required-entry',
            'required'  => true,
        ]);

        $templates = $this->_emailTemplate->toOptionArray();
        $templates[0]['label'] = __('Use Default Configuration');
        $fieldset->addField('email_template', 'select', [
            'name'      => 'email_template',
            'label'     => __('Notification Template'),
            'values'   => $templates,
        ]);

        $fieldset->addField('vendor_shipping', 'hidden', [
            'name' => 'vendor_shipping',
        ]);
/*
        $fieldset->addField('url_key', 'text', array(
            'name'      => 'url_key',
            'label'     => __('URL friendly identifier'),
        ));
*/
        $countries = $this->_sourceCountryFactory->create()
            ->toOptionArray();
        //unset($countries[0]);


        $countryId = $this->_frameworkRegistry->registry('vendor_data') ? $this->_frameworkRegistry->registry('vendor_data')->getCountryId() : null;
        if (!$countryId) {
            $countryId = $this->_scopeConfig->getValue('general/country/default', ScopeInterface::SCOPE_STORE);
        }

        $regionCollection = $this->_modelRegionFactory->create()
            ->getCollection()
            ->addCountryFilter($countryId);

        $regions = $regionCollection->toOptionArray();

        if ($regions) {
            $regions[0]['label'] = __('Please select state...');
        } else {
            $regions = [['value'=>'', 'label'=>'']];
        }

        $fieldset = $form->addFieldset('address_form', [
            'legend'=>__('Shipping Origin Address')
        ]);

        $fieldset->addField('vendor_attn', 'text', [
            'name'      => 'vendor_attn',
            'label'     => __('Attention To'),
        ]);

        $fieldset->addField('street', 'textarea', [
            'name'      => 'street',
            'label'     => __('Street'),
            'class'     => 'required-entry',
            'required'  => true,
            'style'     => 'height:50px',
        ]);

        $fieldset->addField('city', 'text', [
            'name'      => 'city',
            'label'     => __('City'),
            'class'     => 'required-entry',
            'required'  => true,
        ]);

        $fieldset->addField('zip', 'text', [
            'name'      => 'zip',
            'label'     => __('Zip / Postal code'),
        ]);

        $country = $fieldset->addField('country_id', 'select',
            [
                'name' => 'country_id',
                'label' => __('Country'),
                'title' => __('Please select Country'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $countries,
            ]
        );

        $fieldset->addField('region_id', 'select',
            [
                'name' => 'region_id',
                'label' => __('State'),
                'title' => __('Please select State'),
                'values' => $regions,
            ]
        );

        if ($vendor) {
            $form->setValues($vendor->getData());
        }

        if (!$id) {
            $country->setValue($countryId);
        }

        return parent::_prepareForm();
    }

}