<?php

namespace Hhmedia\Editor\Controller\AbstractController;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

abstract class View extends Action\Action
{
    /**
     * @var \Hhmedia\Editor\Controller\AbstractController\EditorLoaderInterface
     */
    protected $editorLoader;
	
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
	 * @param PageFactory $resultPageFactory
     */
    public function __construct(Action\Context $context, EditorLoaderInterface $editorLoader, PageFactory $resultPageFactory)
    {
        $this->editorLoader = $editorLoader;
		$this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Editor view page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->editorLoader->load($this->_request, $this->_response)) {
            return;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
		return $resultPage;
    }
}
