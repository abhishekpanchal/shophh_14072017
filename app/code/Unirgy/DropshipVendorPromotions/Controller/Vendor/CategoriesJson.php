<?php

namespace Unirgy\DropshipVendorPromotions\Controller\Vendor;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Registry;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\SalesRule\Model\RuleFactory;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\Dropship\Helper\Catalog;
use Unirgy\Dropship\Helper\Data as HelperData;

class CategoriesJson extends AbstractVendor
{
    public function execute()
    {
        $this->_helperCatalog->setDesignStore(0, 'adminhtml');
        if ($categoryId = (int) $this->getRequest()->getPost('id')) {
            $this->getRequest()->setParam('id', $categoryId);

            if (!$category = $this->_initCategory()) {
                return;
            }
            return $this->_resultRawFactory->create()->setContents(
                $this->_view->getLayout()->createBlock('\Magento\Catalog\Block\Adminhtml\Category\Tree')
                    ->getTreeJson($category)
            );
        }
    }
}
