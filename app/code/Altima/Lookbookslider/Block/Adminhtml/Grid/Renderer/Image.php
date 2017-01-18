<?php

/**
 * Altima Lookbook Professional Extension
 *
 * Altima web systems.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is available through the world-wide-web at this URL:
 * http://shop.altima.net.au/tos
 *
 * @category   Altima
 * @package    Altima_LookbookProfessional
 * @author     Altima Web Systems http://altimawebsystems.com/
 * @license    http://shop.altima.net.au/tos
 * @email      support@altima.net.au
 * @copyright  Copyright (c) 2016 Altima Web Systems (http://altimawebsystems.com/)
 */

namespace Altima\Lookbookslider\Block\Adminhtml\Grid\Renderer;

class Image extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {

    protected $_slideFactory;
    protected $_lookbooksliderHelper;

    public function __construct(
            \Magento\Backend\Block\Context $context,
            \Magento\Store\Model\StoreManagerInterface $storeManager,
            \Altima\Lookbookslider\Model\SlideFactory $slideFactory,
            \Altima\Lookbookslider\Helper\Data $lookbooksliderHelper,
            array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_storeManager         = $storeManager;
        $this->_slideFactory         = $slideFactory;
        $this->_lookbooksliderHelper = $lookbooksliderHelper;
    }

    public function render(\Magento\Framework\DataObject $row) {
        $slide    = $this->_slideFactory->create()->load($row->getId());
        $srcImage = $this->_lookbooksliderHelper->getResizedUrl($slide->getImagePath(), '200', '150');
        return '<image width="200" height="150" src ="' . $srcImage . '" alt="' . $slide->getImage() . '" >';
    }

}
