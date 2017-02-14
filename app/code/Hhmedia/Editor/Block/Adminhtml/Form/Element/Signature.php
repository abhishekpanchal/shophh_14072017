<?php

/**
 * Editor Form Image File Element Block
 *
 */
namespace Hhmedia\Editor\Block\Adminhtml\Form\Element;

class Signature extends Magento\Framework\Data\Form\Element\Image
{ 
    /**
     * Get image preview url
     *
     * @return string
     */
    protected function _getUrl()
    {
        return $this->getValue();
    }
}
