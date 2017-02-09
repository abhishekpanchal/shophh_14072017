<?php

namespace Hhmedia\Magazine\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;

class MagazineLoader implements MagazineLoaderInterface
{
    /**
     * @var \Hhmedia\Magazine\Model\MagazineFactory
     */
    protected $magazineFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Hhmedia\Magazine\Model\MagazineFactory $magazineFactory
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Hhmedia\Magazine\Model\MagazineFactory $magazineFactory,
        Registry $registry,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->magazineFactory = $magazineFactory;
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

        $magazine = $this->magazineFactory->create()->load($id);
        $this->registry->register('current_magazine', $magazine);
        return true;
    }
}
