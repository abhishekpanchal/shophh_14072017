<?php

namespace Hhmedia\Editor\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

interface EditorLoaderInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Hhmedia\Editor\Model\Editor
     */
    public function load(RequestInterface $request, ResponseInterface $response);
}
