<?php

namespace Unirgy\DropshipBatch\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;

class VendorAutocomplete extends AbstractIndex
{
    /**
     * @var RawFactory
     */
    protected $_resultRawFactory;

    /**
     * @var LayoutFactory
     */
    protected $_viewLayoutFactory;

    public function __construct(Context $context, 
        RawFactory $resultRawFactory, 
        LayoutFactory $viewLayoutFactory)
    {
        $this->_resultRawFactory = $resultRawFactory;
        $this->_viewLayoutFactory = $viewLayoutFactory;

        parent::__construct($context);
    }

    public function execute()
    {
        $this->_resultRawFactory->create()->setContents(
            $this->_viewLayoutFactory->create()
                ->createBlock('')
                ->setVendorPrefix($this->getRequest()->getParam('vendor_name'))
                ->toHtml()
        );
    }
}
