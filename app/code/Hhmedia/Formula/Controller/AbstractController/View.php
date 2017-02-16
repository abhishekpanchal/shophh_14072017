<?php

namespace Hhmedia\Formula\Controller\AbstractController;

use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;

abstract class View extends Action\Action
{
    /**
     * @var \Hhmedia\Formula\Controller\AbstractController\FormulaLoaderInterface
     */
    protected $formulaLoader;
	
	/**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param OrderLoaderInterface $orderLoader
	 * @param PageFactory $resultPageFactory
     */
    public function __construct(Action\Context $context, FormulaLoaderInterface $formulaLoader, PageFactory $resultPageFactory)
    {
        $this->formulaLoader = $formulaLoader;
		$this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Formula view page
     *
     * @return void
     */
    public function execute()
    {
        if (!$this->formulaLoader->load($this->_request, $this->_response)) {
            return;
        }

        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
		return $resultPage;
    }
}
