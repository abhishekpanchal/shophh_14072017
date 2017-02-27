<?php

namespace Unirgy\Dropship\Block\Vendor;

use Magento\Framework\DB\Select;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Unirgy\Dropship\Model\VendorFactory;

class Autocomplete extends Template
{
    /**
     * @var VendorFactory
     */
    protected $_modelVendorFactory;

    public function __construct(Context $context, 
        VendorFactory $modelVendorFactory, 
        array $data = [])
    {
        $this->_modelVendorFactory = $modelVendorFactory;

        parent::__construct($context, $data);
    }

    protected $_vendorPrefix;

    public function setVendorPrefix($vendorPrefix)
    {
        $this->_vendorPrefix = $vendorPrefix;
        return $this;
    }
    public function getVendorPrefix()
    {
        return $this->_vendorPrefix;
    }

    public function getSuggestData()
    {
        $vendors = $this->_modelVendorFactory->create()->getCollection()->setOrder('vendor_name', 'asc')->setPageSize(20);
        $vendors->getSelect()
            ->reset(Select::COLUMNS)
            ->columns(['vendor_name', 'vendor_id'])
            ->where('vendor_name like ?', $this->getVendorPrefix().'%');
        $allVendor = $this->_modelVendorFactory->create()->addData([
            'vendor_id'=>0,
            'vendor_name'=>__('* All Vendors *')
        ]);
        if (false !== strpos($allVendor->getVendorName(), $this->getVendorPrefix())) {
            $vendors->addItem($allVendor);
        }
        return $vendors;
    }

    protected function _toHtml()
    {
        $html = '<ul><li style="display:none"></li>';
        $sd = $this->getSuggestData();
        foreach ($sd as $index => $item) {
            $rowClass = $index%2?'odd':'even';
            if ($index == 0) {
                $rowClass .= ' first';
            }

            if ($index == $sd->count()) {
                $rowClass .= ' last';
            }

            $html .=  '<li style="margin: 0px; min-height: 1.3em" title="'.$item->getId().'" class="'.$rowClass.'">'
                .$this->escapeHtml($item->getVendorName())
                .($index == $sd->count() && $sd->getSize()>$sd->count() ? '<span>...</span></li>' : '</li>');
        }

        $html.= '</ul>';

        return $html;
    }
}