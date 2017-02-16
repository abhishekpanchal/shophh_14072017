<?php

namespace Hhmedia\Formula\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;

class FormulaLoader implements FormulaLoaderInterface
{
    /**
     * @var \Hhmedia\Formula\Model\FormulaFactory
     */
    protected $formulaFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Hhmedia\Formula\Model\FormulaFactory $formulaFactory
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Hhmedia\Formula\Model\FormulaFactory $formulaFactory,
        Registry $registry,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->formulaFactory = $formulaFactory;
        $this->registry = $registry;
        $this->url = $url;
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return bool
     */
    public function load(RequestInterface $request, ResponseInterface $response)
    {
        $id = (int)$request->getParam('id');
        if (!$id) {
            $request->initForward();
            $request->setActionName('noroute');
            $request->setDispatched(false);
            return false;
        }

        $formula = $this->formulaFactory->create()->load($id);
        $this->registry->register('current_formula', $formula);
        return true;
    }
}
