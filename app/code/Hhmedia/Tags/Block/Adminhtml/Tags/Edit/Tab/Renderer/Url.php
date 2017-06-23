<?php
 
namespace Hhmedia\Tags\Block\Adminhtml\Tags\Edit\Tab\Renderer;
 
use Magento\Framework\DataObject;

class Url extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(DataObject $row)
    {
        $tagsId = $row->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $url = $baseUrl.'tags/index/view/id/'.$tagsId;
        return '<a target="_blank" href="'.$url.'">'.$url.'</a>';
    }
}