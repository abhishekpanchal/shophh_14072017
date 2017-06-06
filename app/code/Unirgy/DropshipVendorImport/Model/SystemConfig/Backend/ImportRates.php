<?php

namespace Unirgy\DropshipVendorImport\Model\SystemConfig\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Unirgy\DropshipVendorImport\Model\ImportRatesFactory;

class ImportRates extends Value
{
    /**
     * @var ImportratesFactory
     */
    protected $_importRatesFactory;

    public function __construct(Context $context, 
        Registry $registry, 
        ScopeConfigInterface $config, 
        TypeListInterface $cacheTypeList, 
        ImportRatesFactory $importRatesFactory,
        AbstractResource $resource = null, 
        AbstractDb $resourceCollection = null, 
        array $data = [])
    {
        $this->_importRatesFactory = $importRatesFactory;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function afterSave()
    {
        $this->_importRatesFactory->create()->uploadAndImport($this);
        return $this;
    }
}