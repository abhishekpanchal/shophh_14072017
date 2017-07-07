<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-07-06
 * Time: 13:43
 */

namespace Bluebadger\ConfigurableProduct\Helper;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Image as ProductImage;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    private   $_idcat;
    private   $_is_online_parent = false;
    protected $_categoryFactory;
    protected $_productFactory;
    protected $_product_parent;
    protected $_localeCurrency;
    protected $_storeManager;

    protected $imageHelper;

    public function __construct(
        Context $context,
        CurrencyInterface $localeCurrency,
        StoreManagerInterface $storeManager,
        CategoryFactory $categoryFactory,
        ProductFactory $productFactory,
        Configurable $product_parent,
        Image $imageHelper
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_product_parent = $product_parent;
        $this->_productFactory = $productFactory;
        $this->imageHelper = $imageHelper;
        $this->_localeCurrency = $localeCurrency;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }


    /**
     * @param Product $product
     * @return mixed
     */
    public function getGalleryImage(ProductInterface $product)
    {
        $images = $product->getMediaGalleryImages();
        return $images;
    }

    /**
     * @param Product $variant
     * @param $name_image
     * @param $image_string
     * @param $type
     * @param $size
     * @return ProductImage|bool
     */
    public function _getRolloverImage($variant, $name_image, $image_string, $type, $size ){

        $product = $this->getLoadProduct($variant->getId());
        $collection = $this->getGalleryImage($product);

        foreach ($collection as $image) {
            /** @var $image ProductImage */

            if(strpos($image->getData('file'),$image_string) !==false){
                $img = $image->setData(
                    $name_image,
                    $this->imageHelper->init($product, $type)
                        ->constrainOnly(true)->keepAspectRatio(true)->keepFrame(false)
                        ->setImageFile($image->getFile())
                        ->resize($size,$size)
                        ->getUrl()
                );
                return $img;
            }
        }
        return false;
    }

    /**
     * @param Product $product
     * @return bool
     */
    public function getOnSale($product){
        $on_sale = false;
        if($product->getSpecialPrice() && ($product->getFinalPrice() < $product->getPrice())){
            $on_sale = true;
        }
        return $on_sale;
    }


    /**
     * Get current currency symbol
     *
     * @return string
     */
    public function getSymbolCurrency()
    {
        $code = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        return $this->_localeCurrency->getCurrency($code)->getSymbol();
    }

    /**
     * @param $id
     * @return Product
     */
    public function getLoadProduct($id)
    {
        return $this->_productFactory->create()->load($id);
    }
}