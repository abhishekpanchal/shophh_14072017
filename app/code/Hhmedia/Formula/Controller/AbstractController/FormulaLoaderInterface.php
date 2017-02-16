<?php

namespace Hhmedia\Formula\Controller\AbstractController;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

interface FormulaLoaderInterface
{
    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return \Hhmedia\Formula\Model\Formula
     */
    public function load(RequestInterface $request, ResponseInterface $response);
}
