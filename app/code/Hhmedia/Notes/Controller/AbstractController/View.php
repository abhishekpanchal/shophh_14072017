<?php

namespace Hhmedia\Notes\Controller\AbstractController;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

abstract class View extends Action\Action
{
    /**
     * @var \Hhmedia\Notes\Controller\AbstractController\NotesLoaderInterface
     */
    protected $notesLoader;
	
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
	 * @param PageFactory $resultPageFactory
     */
    public function __construct(Action\Context $context, NotesLoaderInterface $notesLoader, PageFactory $resultPageFactory)
    {
        $this->notesLoader = $notesLoader;
		$this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Notes view page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->notesLoader->load($this->_request, $this->_response)) {
            return;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
		return $resultPage;
    }
}
