<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-07-10
 * Time: 11:01
 */

namespace Bluebadger\Catalog\Model\Import;

/**
 * Import entity product model
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Product extends \Magento\CatalogImportExport\Model\Import\Product
{

    const HH_CATEGORY_SEPARATOR = '|';


    /**
     *
     * Multiple value separator getter.
     * @return string
     */
    public function getMultipleValueSeparator()
    {
        return self::HH_CATEGORY_SEPARATOR;
    }
}