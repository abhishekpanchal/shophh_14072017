<?php

namespace MagicToolbox\MagicScroll\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * MagicToolbox Observer
 *
 */
class FixLayoutBefore implements ObserverInterface
{
    /**magicscroll 
     * Execute method
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     *
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $observer->getLayout();
        $layoutXMLElement = $layout->getNode(null);
        $pathes = [
            '/layout/body/referenceContainer[@name="product.info.media"]' => 'block[@name="product.info.media.magicscroll"]',
            '/layout/body/referenceBlock[@name="product.info.options.wrapper"]' => 'block[@class="MagicToolbox\MagicScroll\Block\Product\View\Type\Configurable"]',
            '/layout/body/referenceBlock[@name="category.product.type.details.renderers"]' => 'block[@name="configurable.magicscroll"]',
        ];

        foreach ($pathes as $searchPath => $checkPath) {
            $nodes = $layoutXMLElement->xpath($searchPath);
            if ($nodes) {
                while(list( , $node) = each($nodes)) {
                    if ($node->xpath($checkPath)) {
                        $body = $layoutXMLElement->addChild('body');
                        $body->appendChild($node);
                        $node->unsetSelf();
                    }
                }
            }
        }

        return $this;
    }
}
