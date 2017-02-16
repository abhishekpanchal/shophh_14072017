<?php

namespace Hhmedia\Formula\Controller\Adminhtml\Index;

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
        return $this->_authorization->isAllowed('Hhmedia_Formula::formula_manage');
    }

    /**
     * Formula List action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Hhmedia_Formula::formula_manage'
        )->addBreadcrumb(
            __('Formula'),
            __('Formula')
        )->addBreadcrumb(
            __('Manage Formula'),
            __('Manage Formula')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Formula'));
        return $resultPage;
    }
}
