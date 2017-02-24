<?php

namespace Unirgy\DropshipVendorTax\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Model\Exception;
use Unirgy\Dropship\Model\VendorFactory;

class MassUpdateVendorTaxClass extends AbstractIndex
{
    /**
     * @var VendorFactory
     */
    protected $_vendorFactory;

    public function __construct(
        Context $context,
        VendorFactory $modelVendorFactory
    )
    {
        $this->_vendorFactory = $modelVendorFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $modelIds = (array)$this->getRequest()->getParam('vendor');
        $vTaxClass = (string)$this->getRequest()->getParam('vendor_tax_class');

        try {
            foreach ($modelIds as $modelId) {
                $this->_vendorFactory->create()->load($modelId)->setVendorTaxClass($vTaxClass)->save();
            }
            $this->messageManager->addSuccess(
                __('Total of %d record(s) were successfully updated', count($modelIds))
            );
        }
        catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        }
        catch (\Exception $e) {
            $this->messageManager->addException($e, __('There was an error while updating vendor(s) tax class'));
        }

        $this->_redirect('udropship/vendor/');
    }
}
