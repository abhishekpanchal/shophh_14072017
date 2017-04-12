<?php

namespace Hhmedia\Collection\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;

class CollectionLoader implements CollectionLoaderInterface
{
    /**
     * @var \Hhmedia\Collection\Model\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Hhmedia\Collection\Model\CollectionFactory $collectionFactory
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Hhmedia\Collection\Model\CollectionFactory $collectionFactory,
        Registry $registry,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->collectionFactory = $collectionFactory;
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

        $collection = $this->collectionFactory->create()->load($id);
        $this->registry->register('current_collection', $collection);
        return true;
    }
}
