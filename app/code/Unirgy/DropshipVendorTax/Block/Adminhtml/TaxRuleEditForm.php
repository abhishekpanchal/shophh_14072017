<?php

namespace Unirgy\DropshipVendorTax\Block\Adminhtml;

class TaxRuleEditForm extends \Magento\Tax\Block\Adminhtml\Rule\Edit\Form
{
    protected $_hlp;
    protected $_udtaxSrc;
    public function __construct(
        \Unirgy\Dropship\Helper\Data $udropshipHelper,
        \Unirgy\DropshipVendorTax\Model\Source $udtaxSource,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Tax\Model\Rate\Source $rateSource,
        \Magento\Tax\Api\TaxRuleRepositoryInterface $ruleService,
        \Magento\Tax\Api\TaxClassRepositoryInterface $taxClassService,
        \Magento\Tax\Model\TaxClass\Source\Customer $customerTaxClassSource,
        \Magento\Tax\Model\TaxClass\Source\Product $productTaxClassSource,
        array $data = []
    ) {
        $this->_hlp = $udropshipHelper;
        $this->_udtaxSrc = $udtaxSource;
        parent::__construct($context, $registry, $formFactory, $rateSource, $ruleService, $taxClassService, $customerTaxClassSource, $productTaxClassSource, $data);
        $this->setData('module_name', 'Magento_Tax');
    }
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $taxRuleId = $this->_coreRegistry->registry('tax_rule_id');
        try {
            $taxRule = $this->ruleService->get($taxRuleId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            /** Tax rule not found */
        }
        $sessionFormValues = (array)$this->_coreRegistry->registry('tax_rule_form_data');
        $taxRuleData = isset($taxRule) ? $this->extractTaxRuleData($taxRule) : [];
        $formValues = array_merge($taxRuleData, $sessionFormValues);

        $fieldset = $this->getForm()->getElement('base_fieldset');

        $selectConfig = $this->getTaxClassSelectConfig(\Unirgy\DropshipVendorTax\Model\Source::TAX_CLASS_TYPE_VENDOR);
        $options = $this->_udtaxSrc->setPath('vendor_tax_class')->toOptionArray(false);
        if (!empty($options)) {
            $selected = $options[0];
        } else {
            $selected = null;
        }

        // Use the rule data or pick the first class in the list
        $selectedVendorTax = isset($formValues['tax_vendor_class'])
            ? $formValues['tax_vendor_class']
            : $selected;
        $fieldset->addField(
            'tax_vendor_class',
            'editablemultiselect',
            [
                'name' => 'tax_vendor_class',
                'label' => __('Vendor Tax Class'),
                'class' => 'required-entry',
                'values' => $options,
                'value' => $selectedVendorTax,
                'required' => true,
                'select_config' => $selectConfig
            ],
            false,
            true
        );
        return $this;
    }
    protected function extractTaxRuleData($taxRule)
    {
        $taxRuleData = [
            'code' => $taxRule->getCode(),
            'tax_customer_class' => $taxRule->getCustomerTaxClassIds(),
            'tax_product_class' => $taxRule->getProductTaxClassIds(),
            'tax_vendor_class' => $taxRule->getVendorTaxClassIds(),
            'tax_rate' => $taxRule->getTaxRateIds(),
            'priority' => $taxRule->getPriority(),
            'position' => $taxRule->getPosition(),
            'calculate_subtotal' => $taxRule->getCalculateSubtotal(),
        ];
        return $taxRuleData;
    }
}