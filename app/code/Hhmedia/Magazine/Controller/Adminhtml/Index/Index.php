<?php

namespace Hhmedia\Magazine\Controller\Adminhtml\Index;

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
        return $this->_authorization->isAllowed('Hhmedia_Magazine::magazine_manage');
    }

    /**
     * Magazine List action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Hhmedia_Magazine::magazine_manage'
        )->addBreadcrumb(
            __('Magazine'),
            __('Magazine')
        )->addBreadcrumb(
            __('Manage Magazine'),
            __('Manage Magazine')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Magazine'));
        return $resultPage;
    }
}
