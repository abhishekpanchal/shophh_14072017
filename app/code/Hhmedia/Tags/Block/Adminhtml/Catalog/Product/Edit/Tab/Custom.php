<?php


namespace Hhmedia\Tags\Block\Adminhtml\Catalog\Product\Edit\Tab;


class Custom extends \Magento\Backend\Block\Widget implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    protected $_template = 'catalog/product/edit/tab/custom.phtml';


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    protected function _prepareLayout()
    {

        return parent::_prepareLayout();
    }
}