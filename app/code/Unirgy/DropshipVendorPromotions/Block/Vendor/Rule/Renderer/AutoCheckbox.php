<?php

namespace Unirgy\DropshipVendorPromotions\Block\Vendor\Rule\Renderer;

use Magento\Backend\Block\AbstractBlock;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Checkbox;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;

class AutoCheckbox
    extends AbstractBlock
    implements RendererInterface
{
    /**
     * Checkbox render function
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $checkbox = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Data\Form\Element\Checkbox', ['data'=>$element->getData()]);
        $checkbox->setForm($element->getForm());

        $elementHtml = $checkbox->getElementHtml() . sprintf(
                '<label for="%s"><b>%s</b></label><p class="note">%s</p>&nbsp;',
                $element->getHtmlId(), $element->getLabel(), $element->getNote()
            );
        $html  = $elementHtml;

        return $html;
    }

}
