<?php

namespace Unirgy\DropshipBatch\Controller\Vendor\Batch;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\DropshipBatch\Model\BatchFactory;
use Unirgy\Dropship\Helper\Data as HelperData;

class ExportOrders extends AbstractBatch
{
    /**
     * @var BatchFactory
     */
    protected $_batchFactory;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    public function __construct(
        BatchFactory $batchFactory,
        EncoderInterface $jsonEncoder,
        Context $context,
        ScopeConfigInterface $scopeConfig,
        DesignInterface $viewDesignInterface,
        StoreManagerInterface $storeManager,
        LayoutFactory $viewLayoutFactory,
        Registry $registry,
        ForwardFactory $resultForwardFactory,
        HelperData $helper,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\HTTP\Header $httpHeader
    )
    {
        $this->_batchFactory = $batchFactory;
        $this->_jsonEncoder = $jsonEncoder;

        parent::__construct($context, $scopeConfig, $viewDesignInterface, $storeManager, $viewLayoutFactory, $registry, $resultForwardFactory, $helper, $resultPageFactory, $resultRawFactory, $httpHeader);
    }

    public function execute()
    {
        $result = [];
        try {
            $udpos = $this->getVendorPoCollection();
            if (!$udpos->getSize()) {
                throw new \Exception('No purchase orders found for these criteria');
            }

            $batch = $this->_batchFactory->create()->addData([
                'batch_type' => 'export_orders',
                'batch_status' => 'pending',
                'vendor_id' => $this->_getSession()->getVendor()->getId(),
                'use_custom_template' => $this->scopeConfig->getValue('udropship/batch/statement_export_template', ScopeInterface::SCOPE_STORE)
            ]);

            foreach ($udpos as $po) {
                $batch->addPOToExport($po);
            }

            $output = $batch->getAdapter()->renderOutput();

            $this->_hlp->sendDownload('orders_export.csv', $output, 'text/csv');
            return;

        } catch (\Exception $e) {
            if ($this->getRequest()->getParam('use_json_response')) {
                $result = [
                    'error'=>true,
                    'message'=>$e->getMessage()
                ];
            } else {
                $this->messageManager->addError(__($e->getMessage()));
            }
        }
        if ($this->getRequest()->getParam('use_json_response')) {
            return $this->_resultRawFactory->create()->setContents(
                $this->_jsonEncoder->encode($result)
            );
        } else {
            return $this->_redirect('udpo/vendor/', ['_current'=>true, '_query'=>['submit_action'=>'']]);
        }
    }
}
