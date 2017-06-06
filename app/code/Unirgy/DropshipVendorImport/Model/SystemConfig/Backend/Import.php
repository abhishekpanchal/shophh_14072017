<?php

namespace Unirgy\DropshipVendorImport\Model\SystemConfig\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Unirgy\DropshipVendorImport\Model\ImportFactory;

class Import extends Value
{
    /**
     * @var ImportFactory
     */
    protected $_importFactory;

    public function __construct(Context $context, 
        Registry $registry, 
        ScopeConfigInterface $config, 
        TypeListInterface $cacheTypeList, 
        ImportFactory $modelImportFactory, 
        AbstractResource $resource = null, 
        AbstractDb $resourceCollection = null, 
        array $data = [])
    {
        $this->_importFactory = $modelImportFactory;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function afterSave()
    {
        $this->_importFactory->create()->uploadAndImport($this);
        return $this;
    }
}