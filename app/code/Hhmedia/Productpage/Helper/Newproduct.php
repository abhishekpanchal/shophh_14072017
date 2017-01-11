<?php

namespace Hhmedia\Productpage\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product as ModelProduct;
use Magento\Store\Model\Store;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Newproduct extends \Magento\Framework\Url\Helper\Data
{

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        TimezoneInterface $localeDate
    ) {
        $this->localeDate = $localeDate;
        parent::__construct($context);
    }

    public function isProductNew(ModelProduct $product)
    {
        $newsFromDate = $product->getNewsFromDate();
        $newsToDate = $product->getNewsToDate();
        if (!$newsFromDate && !$newsToDate) {
            return false;
        }

        return $this->localeDate->isScopeDateInInterval(
            $product->getStore(),
            $newsFromDate,
            $newsToDate
        );
    }
}