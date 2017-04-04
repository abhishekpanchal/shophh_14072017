<?php

namespace Hhmedia\Collection\Controller\AbstractController;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

abstract class View extends Action\Action
{
    /**
     * @var \Hhmedia\Collection\Controller\AbstractController\CollectionLoaderInterface
     */
    protected $collectionLoader;
	
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
	 * @param PageFactory $resultPageFactory
     */
    public function __construct(Action\Context $context, CollectionLoaderInterface $collectionLoader, PageFactory $resultPageFactory)
    {
        $this->collectionLoader = $collectionLoader;
		$this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Collection view page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->collectionLoader->load($this->_request, $this->_response)) {
            return;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
		return $resultPage;
    }
}
