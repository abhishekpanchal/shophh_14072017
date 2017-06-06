<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipVendorTax
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

/**
* Currently not in use
*/
namespace Unirgy\DropshipVendorTax\Model;

use Unirgy\DropshipVendorTax\Helper\Data as DropshipVendorTaxHelperData;
use Unirgy\DropshipVendorTax\Model\Source as ModelSource;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Source\AbstractSource;

class Source extends AbstractSource
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var DropshipVendorTaxHelperData
     */
    protected $_udtaxHlp;

    public function __construct(
        HelperData $helperData, 
        DropshipVendorTaxHelperData $dropshipVendorTaxHelperData, 
        array $data = []
    )
    {
        $this->_hlp = $helperData;
        $this->_udtaxHlp = $dropshipVendorTaxHelperData;

        parent::__construct($data);
    }

    const TAX_CLASS_TYPE_VENDOR = 'VENDOR';
    public function toOptionHash($selector=false)
    {
        $hlp = $this->_hlp;
        $hlpv = $this->_udtaxHlp;

        switch ($this->getPath()) {

        case 'vendor_tax_class':
            $options = $this->_hlp->createObj('\Magento\Tax\Model\ResourceModel\TaxClass\Collection')
                ->setClassTypeFilter(ModelSource::TAX_CLASS_TYPE_VENDOR)
                ->load()
                ->toOptionHash();

            $options = ['0' => __('None')] + $options;
            break;

        default:
            throw new \Exception(__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = [''=>__('* Please select')] + $options;
        }

        return $options;
    }
}