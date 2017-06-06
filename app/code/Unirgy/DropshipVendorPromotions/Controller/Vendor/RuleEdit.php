<?php

namespace Unirgy\DropshipVendorPromotions\Controller\Vendor;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;

class RuleEdit extends AbstractVendor
{
    public function execute()
    {
        $session = ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session');
        try {
            $this->checkRule();
            $this->_renderPage(null, 'udpromo');
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $this->_redirectRuleAfterPost();
        }
    }
}
