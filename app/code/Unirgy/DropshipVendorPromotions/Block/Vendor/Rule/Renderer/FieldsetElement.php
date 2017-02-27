<?php

namespace Unirgy\DropshipVendorPromotions\Block\Vendor\Rule\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Unirgy\Dropship\Helper\Catalog;

class FieldsetElement extends Template implements RendererInterface
{
    /**
     * @var Catalog
     */
    protected $_helperCatalog;

    public function __construct(Context $context, 
        Catalog $helperCatalog, 
        array $data = [])
    {
        $this->_helperCatalog = $helperCatalog;

        parent::__construct($context, $data);
    }

    protected $_element;

    protected function _construct()
    {
        $this->setTemplate('Unirgy_DropshipVendorPromotions::unirgy/udpromo/vendor/rule/renderer/fieldset_element.phtml');
    }

    public function getElement()
    {
        return $this->_element;
    }

    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        $element->addClass('udvalidate-'.$element->getId());
        return $this->toHtml();
    }

    public function getElementHtml()
    {
        //if ($this->_element->getSwitchAdminhtml()) $this->_helperCatalog->setDesignStore(0, 'adminhtml');
        $html = $this->_element->getElementHtml();
        //if ($this->_element->getSwitchAdminhtml()) $this->_helperCatalog->setDesignStore();
        return $html;
    }
}