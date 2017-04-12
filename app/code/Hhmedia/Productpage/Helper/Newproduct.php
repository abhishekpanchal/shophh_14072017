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

    function limit_review($text, $limit) {
        if (str_word_count($text, 0) > $limit) {
            $words = str_word_count($text, 2);
            $pos = array_keys($words);
            $shortReview = substr($text, 0, $pos[$limit]).'...';
            $fullReview = substr($text, $pos[$limit+1], end($pos));
            return "<span class='short-review'>".$shortReview."<a href='#' class='review-read-more'><span class='block'>Read More</span></a></span><span class='full-review'>".$fullReview."<a href='#' class='review-read-less'><span class='block'>Read Less</span></a></span>";
        }else{
            return "<span class='short-review'>".$text."</span>";
        }
    }

    function limit_description($text, $limit) {
        if (str_word_count($text, 0) > $limit) {
            $words = str_word_count($text, 2);
            $pos = array_keys($words);
            $shortDescription = substr($text, 0, $pos[$limit]).'...';
            return $shortDescription;
        }else{
            return $text;
        }
    }
}