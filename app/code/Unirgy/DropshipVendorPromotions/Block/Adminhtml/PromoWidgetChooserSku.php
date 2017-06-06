<?php

namespace Unirgy\DropshipVendorPromotions\Block\Adminhtml;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Helper\Data as HelperData;
use Magento\CatalogRule\Block\Adminhtml\Promo\Widget\Chooser\Sku;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Magento\Framework\App\ObjectManager;

class PromoWidgetChooserSku extends Sku
{
    /**
     * @var Collection
     */
    protected $_productCollection;

    protected $_template = 'Unirgy_DropshipVendorPromotions::widget/grid/extended.phtml';

    public function __construct(Context $context, 
        HelperData $backendHelper, 
        CollectionFactory $eavAttSetCollection, 
        ProductCollectionFactory $cpCollection, 
        Type $catalogType, 
        Collection $productCollection, 
        array $data = [])
    {
        $this->_productCollection = $productCollection;

        parent::__construct($context, $backendHelper, $eavAttSetCollection, $cpCollection, $catalogType, $data);
    }

    protected function _prepareCollection()
    {
        $collection = $this->_productCollection
            ->setStoreId(0)
            ->addAttributeToSelect('name', 'type_id', 'attribute_set_id')
            ->addAttributeToFilter('udropship_vendor', ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session')->getVendorId());

        $this->setCollection($collection);

        return Grid::_prepareCollection();
    }
}