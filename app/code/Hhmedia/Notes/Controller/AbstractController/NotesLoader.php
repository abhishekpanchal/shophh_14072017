<?php

namespace Hhmedia\Notes\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Registry;

class NotesLoader implements NotesLoaderInterface
{
    /**
     * @var \Hhmedia\Notes\Model\NotesFactory
     */
    protected $notesFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @param \Hhmedia\Notes\Model\NotesFactory $notesFactory
     * @param OrderViewAuthorizationInterface $orderAuthorization
     * @param Registry $registry
     * @param \Magento\Framework\UrlInterface $url
     */
    public function __construct(
        \Hhmedia\Notes\Model\NotesFactory $notesFactory,
        Registry $registry,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->notesFactory = $notesFactory;
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

        $notes = $this->notesFactory->create()->load($id);
        $this->registry->register('current_notes', $notes);
        return true;
    }
}
