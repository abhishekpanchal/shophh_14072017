<?php

namespace Hhmedia\Magazine\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

interface MagazineLoaderInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Hhmedia\Magazine\Model\Magazine
     */
    public function load(RequestInterface $request, ResponseInterface $response);
}
