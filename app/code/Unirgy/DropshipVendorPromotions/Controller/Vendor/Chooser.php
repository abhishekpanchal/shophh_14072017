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

class Chooser extends AbstractVendor
{
    public function execute()
    {
        $this->_helperCatalog->setDesignStore(0, 'adminhtml');
        $request = $this->getRequest();

        switch ($request->getParam('attribute')) {
            case 'sku':
                $block = $this->_view->getLayout()->createBlock(
                    '\Unirgy\DropshipVendorPromotions\Block\Adminhtml\PromoWidgetChooserSku', 'promo_widget_chooser_sku',
                    ['data' => ['js_form_object' => $request->getParam('form')],
                    ]);
                break;

            case 'category_ids':
                $ids = $request->getParam('selected', []);
                if (is_array($ids)) {
                    foreach ($ids as $key => &$id) {
                        $id = (int) $id;
                        if ($id <= 0) {
                            unset($ids[$key]);
                        }
                    }

                    $ids = array_unique($ids);
                } else {
                    $ids = [];
                }


                $block = $this->_view->getLayout()->createBlock(
                    '\Unirgy\DropshipVendorPromotions\Block\Adminhtml\CategoryCheckboxesTree', 'promo_widget_chooser_category_ids',
                    ['data' => ['js_form_object' => $request->getParam('form')]]
                )
                    ->setCategoryIds($ids)
                ;
                break;

            default:
                $block = false;
                break;
        }

        if ($block) {
            return $this->_resultRawFactory->create()->setContents($block->toHtml());
        }
    }
}
