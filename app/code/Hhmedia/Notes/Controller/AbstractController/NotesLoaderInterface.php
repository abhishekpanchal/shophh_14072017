<?php

namespace Hhmedia\Notes\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

interface NotesLoaderInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Hhmedia\Notes\Model\Notes
     */
    public function load(RequestInterface $request, ResponseInterface $response);
}
