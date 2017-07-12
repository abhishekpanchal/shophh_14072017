<?php

namespace Hhmedia\Checkout\Model;


use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /** @var LayoutInterface  */
    protected $_layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->_layout = $layout;
    }

    public function getConfig()
    {
        $cmsBlockId = 13; // id of cms block to use

        return [
            'cms_block_message' => $this->_layout->createBlock('Magento\Cms\Block\Block')->setBlockId($cmsBlockId)->toHtml()
        ];
    }
}
