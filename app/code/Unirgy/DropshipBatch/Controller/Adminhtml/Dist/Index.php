<?php

namespace Unirgy\DropshipBatch\Controller\Adminhtml\Dist;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\LayoutFactory;

class Index extends AbstractDist
{
    public function execute()
    {
        $page = $this->_initAction();
        return $page->addContent($page->getLayout()->createBlock('\Unirgy\DropshipBatch\Block\Adminhtml\Dist'));
    }
}
