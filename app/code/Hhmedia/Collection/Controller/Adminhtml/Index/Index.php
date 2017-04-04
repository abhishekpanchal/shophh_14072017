<?php

namespace Hhmedia\Collection\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }
	
    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Hhmedia_Collection::collection_manage');
    }

    /**
     * Collection List action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Hhmedia_Collection::collection_manage'
        )->addBreadcrumb(
            __('Collection'),
            __('Collection')
        )->addBreadcrumb(
            __('Manage Collection'),
            __('Manage Collection')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Collection'));
        return $resultPage;
    }
}
