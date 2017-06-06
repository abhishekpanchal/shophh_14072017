<?php

namespace Unirgy\DropshipVendorTax\Model;

class TaxClassTypeVendor extends \Magento\Tax\Model\TaxClass\AbstractType
{
    /**
     * @var \Unirgy\Dropship\Model\Vendor
     */
    protected $_modelVendor;

    /**
     * Class Type
     *
     * @var string
     */
    protected $_classType = \Unirgy\DropshipVendorTax\Model\Source::TAX_CLASS_TYPE_VENDOR;

    /**
     * @param \Magento\Tax\Model\Calculation\Rule $calculationRule
     * @param \Unirgy\Dropship\Model\Vendor $modelProduct
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Model\Calculation\Rule $calculationRule,
        \Unirgy\Dropship\Model\Vendor $modelProduct,
        array $data = []
    ) {
        parent::__construct($calculationRule, $data);
        $this->_modelVendor = $modelProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function isAssignedToObjects()
    {
        return $this->_modelVendor->getCollection()->addFieldToFilter('vendor_tax_class', $this->getId())
            ->getSize() > 0;
    }

    /**
     * Get Name of Objects that use this Tax Class Type
     *
     * @return \Magento\Framework\Phrase
     */
    public function getObjectTypeName()
    {
        return __('vendor');
    }
}
