<?php

namespace Unirgy\DropshipBatch\Controller\Vendor\Batch;

use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\DropshipBatch\Helper\Data as DropshipBatchHelperData;
use Unirgy\Dropship\Helper\Data as HelperData;

class ImportOrdersPost extends AbstractBatch
{
    /**
     * @var DropshipBatchHelperData
     */
    protected $_bHlp;

    public function __construct(
        DropshipBatchHelperData $dropshipBatchHelperData,
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
        $this->_bHlp = $dropshipBatchHelperData;

        parent::__construct($context, $scopeConfig, $viewDesignInterface, $storeManager, $viewLayoutFactory, $registry, $resultForwardFactory, $helper, $resultPageFactory, $resultRawFactory, $httpHeader);
    }

	public function execute()
    {
    	$r = $this->getRequest();
    	$hlp = $this->_hlp;
    	$bHlp = $this->_bHlp;
    	try {
    		$r->setParam('vendor_id', $this->_getSession()->getVendor()->getId());
    		$r->setParam('batch_type', 'import_orders');
        	$bHlp->processPost();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            if ($bHlp->getBatch()) {
                $this->messageManager->addError(
            		$bHlp->getBatch()->getErrorInfo($e->getMessage())
            	);
            }
        }
        if (($batch = $bHlp->getBatch())) {
            $batchStatus = $batch->getBatchStatus();
            if (in_array($batchStatus, array('success','partial'))) {
                $this->messageManager->addSuccess(__('Processed %1 import rows', $batch->getNumRows()));
            }
            if (in_array($batchStatus, array('error','partial'))) {
                $this->messageManager->addError(nl2br($batch->getErrorInfo()));
            }
        }
        return $this->_redirect('udbatch/vendor_batch/importOrders');
    }
}
