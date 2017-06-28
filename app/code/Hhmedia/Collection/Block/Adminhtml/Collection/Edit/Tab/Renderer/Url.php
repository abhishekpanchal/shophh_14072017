<?php
 
namespace Hhmedia\Collection\Block\Adminhtml\Collection\Edit\Tab\Renderer;
 
use Magento\Framework\DataObject;

class Url extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(DataObject $row)
    {
        $collectionId = $row->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $url = $baseUrl.'collection/index/view/id/'.$collectionId;
        return '<a target="_blank" href="'.$url.'">'.$url.'</a>';
    }
}