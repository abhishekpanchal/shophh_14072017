<?php

namespace Hhmedia\Magazine\Controller\AbstractController;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

abstract class View extends Action\Action
{
    /**
     * @var \Hhmedia\Magazine\Controller\AbstractController\MagazineLoaderInterface
     */
    protected $magazineLoader;
	
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
	 * @param PageFactory $resultPageFactory
     */
    public function __construct(Action\Context $context, MagazineLoaderInterface $magazineLoader, PageFactory $resultPageFactory)
    {
        $this->magazineLoader = $magazineLoader;
		$this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Magazine view page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->magazineLoader->load($this->_request, $this->_response)) {
            return;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
		return $resultPage;
    }
}
