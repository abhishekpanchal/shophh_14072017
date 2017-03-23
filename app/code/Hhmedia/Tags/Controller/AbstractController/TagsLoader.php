<?php

namespace Hhmedia\Tags\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;

class TagsLoader implements TagsLoaderInterface
{
    /**
     * @var \Hhmedia\Tags\Model\TagsFactory
     */
    protected $tagsFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Hhmedia\Tags\Model\TagsFactory $tagsFactory
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Hhmedia\Tags\Model\TagsFactory $tagsFactory,
        Registry $registry,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->tagsFactory = $tagsFactory;
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

        $tags = $this->tagsFactory->create()->load($id);
        $this->registry->register('current_tags', $tags);
        return true;
    }
}
