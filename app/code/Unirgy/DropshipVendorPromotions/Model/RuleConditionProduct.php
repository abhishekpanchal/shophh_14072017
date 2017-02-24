<?php

namespace Unirgy\DropshipVendorPromotions\Model;

use Magento\Backend\Helper\Data as HelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product as ResourceModelProduct;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Registry;
use Magento\Rule\Model\Condition\Context;
use Magento\SalesRule\Model\Rule\Condition\Product;

class RuleConditionProduct extends Product
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;
    protected $_url;
    protected $_backendUrl;

    public function __construct(
        \Magento\Framework\Url $url,
        \Magento\Backend\Model\Url $backendUrl,
        Context $context,
        HelperData $backendData, 
        Config $config, 
        ProductFactory $productFactory, 
        ProductRepositoryInterface $productRepository, 
        ResourceModelProduct $productResource, 
        Collection $attrSetCollection, 
        FormatInterface $localeFormat, 
        Registry $frameworkRegistry, 
        array $data = [])
    {
        $this->_coreRegistry = $frameworkRegistry;
        $this->_backendUrl = $backendUrl;
        $this->_url = $url;

        parent::__construct($context, $backendData, $config, $productFactory, $productRepository, $productResource, $attrSetCollection, $localeFormat, $data);
    }

    public function getValueElementChooserUrl()
    {
        $isUdpromo = $this->_coreRegistry->registry('is_udpromo_vendor');
        $url = '';
        switch ($this->getAttribute()) {
            case 'sku': case 'category_ids':
            if ($isUdpromo) {
                $url = 'udpromo/vendor/chooser'.'/attribute/'.$this->getAttribute();
            } else {
                $url = 'catalog_rule/promo_widget/chooser/attribute/' . $this->getAttribute();
            }
            if ($this->getJsFormObject()) {
                $url .= '/form/'.$this->getJsFormObject();
            }
            if ($isUdpromo) {
                $url = $this->_url->getUrl($url);
            } else {
                $url = $this->_backendUrl->getUrl($url);
            }
            break;
        }
        return $url;
    }
}