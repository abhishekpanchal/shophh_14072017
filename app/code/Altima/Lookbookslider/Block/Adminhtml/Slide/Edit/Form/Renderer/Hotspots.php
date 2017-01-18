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

namespace Altima\Lookbookslider\Block\Adminhtml\Slide\Edit\Form\Renderer;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Hotspots extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements
\Magento\Framework\Data\Form\Element\Renderer\RendererInterface {

    protected $_element  = null;
    protected $_template = 'Altima_Lookbookslider::renderer/form/hotspots.phtml';
    protected $_lookbooksliderHelper;

    public function __construct(
            \Magento\Backend\Block\Widget\Context $context,
            \Magento\Framework\Registry $registry,
            \Altima\Lookbookslider\Helper\Data $lookbooksliderHelper,
            array $data = []
    ) {
        $this->_lookbooksliderHelper = $lookbooksliderHelper;
        $this->_coreRegistry         = $registry;
        parent::__construct($context, $data);
    }

    public function render(AbstractElement $element) {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function setElement(AbstractElement $element) {
        $this->_element = $element;
        return $this;
    }

    public function getElement() {
        return $this->_element;
    }

    public function getValues() {
        return $this->getElement()->getValue();
    }

    public function getAddButtonHtml() {
        return $this->getChildHtml('add_button');
    }

    public function getHotspotIcon() {
        return $this->_lookbooksliderHelper->getHotspotIcon();
    }

    public function getInterdictOverlap() {
        //      return $this->_lookbookHelper->getInterdictOverlap();
    }

}
