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

class Lookbookimage extends \Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element implements
\Magento\Framework\Data\Form\Element\Renderer\RendererInterface {

    protected $_coreRegistry = null;
    protected $_sliderFactory;
    protected $_element      = null;
    protected $_template     = 'Altima_Lookbookslider::renderer/form/lookbookimage.phtml';

    public function __construct(
            \Altima\Lookbookslider\Model\SliderFactory $sliderFactory,
            \Magento\Backend\Block\Widget\Context $context,
            \Magento\Framework\Registry $registry,
            array $data = []
    ) {
        $this->_coreRegistry  = $registry;
        $this->_sliderFactory = $sliderFactory;
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

    public function getSize() {
        $model  = $this->_coreRegistry->registry('current_model');
        $slider = $this->_sliderFactory->create()->load($model->getSliderId());

        return array('width' => $slider->getWidth(), 'height' => $slider->getHeight());
    }

    protected function _getRegistry() {
        if (is_null($this->_coreRegistry)) {
            $this->_coreRegistry = $this->_objectManager->get('\Magento\Framework\Registry');
        }
        return $this->_coreRegistry;
    }

}
