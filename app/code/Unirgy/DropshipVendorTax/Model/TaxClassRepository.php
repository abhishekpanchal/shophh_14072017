<?php

namespace Unirgy\DropshipVendorTax\Model;

use Magento\Framework\Exception\InputException;
use Magento\Tax\Api\TaxClassManagementInterface;
use Magento\Tax\Model\ClassModel;

class TaxClassRepository extends \Magento\Tax\Model\TaxClass\Repository
{
    protected function validateTaxClassData(\Magento\Tax\Api\Data\TaxClassInterface $taxClass)
    {
        $exception = new InputException();

        if (!\Zend_Validate::is(trim($taxClass->getClassName()), 'NotEmpty')) {
            $exception->addError(__(InputException::REQUIRED_FIELD, ['fieldName' => ClassModel::KEY_NAME]));
        }

        $classType = $taxClass->getClassType();
        if (!\Zend_Validate::is(trim($classType), 'NotEmpty')) {
            $exception->addError(__(InputException::REQUIRED_FIELD, ['fieldName' => ClassModel::KEY_TYPE]));
        } elseif ($classType !== TaxClassManagementInterface::TYPE_CUSTOMER
            && $classType !== TaxClassManagementInterface::TYPE_PRODUCT
            && $classType !== \Unirgy\DropshipVendorTax\Model\Source::TAX_CLASS_TYPE_VENDOR
        ) {
            $exception->addError(
                __(
                    InputException::INVALID_FIELD_VALUE,
                    ['fieldName' => ClassModel::KEY_TYPE, 'value' => $classType]
                )
            );
        }

        if ($exception->wasErrorAdded()) {
            throw $exception;
        }
    }
}