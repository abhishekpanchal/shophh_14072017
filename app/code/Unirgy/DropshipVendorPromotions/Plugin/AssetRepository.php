<?php

namespace Unirgy\DropshipVendorPromotions\Plugin;

class AssetRepository
{
    protected $_prependList = [
        'images/rule_component_apply.gif',
        'images/rule_component_add.gif',
        'images/rule_component_remove.gif',
        'images/rule_chooser_trigger.gif'
    ];
    public function beforeGetUrl(\Magento\Framework\View\Asset\Repository $subject, $fileId)
    {
        if (in_array($fileId, $this->_prependList)) {
            $fileId = 'Unirgy_Dropship::'.$fileId;
        }
        return [$fileId];
    }
    public function beforeGetUrlWithParams(\Magento\Framework\View\Asset\Repository $subject, $fileId, array $params)
    {
        if (in_array($fileId, $this->_prependList)) {
            $fileId = 'Unirgy_Dropship::'.$fileId;
        }
        return [$fileId, $params];
    }
}