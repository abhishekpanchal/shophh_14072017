<?php

namespace Unirgy\DropshipVendorPromotions\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Registry;
use Unirgy\Dropship\Model\Source;

class AdminhtmlPromoQuoteEditTabMainPrepareForm extends AbstractObserver implements ObserverInterface
{
    /**
     * @var Source
     */
    protected $_src;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    public function __construct(
        Source $modelSource,
        Registry $frameworkRegistry)
    {
        $this->_src = $modelSource;
        $this->_coreRegistry = $frameworkRegistry;

    }

    public function execute(Observer $observer)
    {
        $options = [''=>'']+$this->_src->setPath('vendors')->toOptionHash();
        $value = null;
        if ($this->_coreRegistry->registry('current_promo_quote_rule')) {
            $value = $this->_coreRegistry->registry('current_promo_quote_rule')->getUdropshipVendor();
        }
        $observer->getForm()->getElement('base_fieldset')
            ->addField('udropship_vendor', 'select', [
                'label'     => __('Dropship Vendor'),
                'title'     => __('Dropship Vendor'),
                'name'      => 'udropship_vendor',
                'options' => $options,
                'value' => $value
        ]);
    }
}
