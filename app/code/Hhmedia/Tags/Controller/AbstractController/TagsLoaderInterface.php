<?php

namespace Hhmedia\Tags\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

interface TagsLoaderInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Hhmedia\Tags\Model\Tags
     */
    public function load(RequestInterface $request, ResponseInterface $response);
}
