<?php

namespace Hhmedia\Editor\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;

class EditorLoader implements EditorLoaderInterface
{
    /**
     * @var \Hhmedia\Editor\Model\EditorFactory
     */
    protected $editorFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Hhmedia\Editor\Model\EditorFactory $editorFactory
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Hhmedia\Editor\Model\EditorFactory $editorFactory,
        Registry $registry,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->editorFactory = $editorFactory;
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

        $editor = $this->editorFactory->create()->load($id);
        $this->registry->register('current_editor', $editor);
        return true;
    }
}
