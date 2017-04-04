<?php

namespace Hhmedia\Collection\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

interface CollectionLoaderInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Hhmedia\Collection\Model\Collection
     */
    public function load(RequestInterface $request, ResponseInterface $response);
}
