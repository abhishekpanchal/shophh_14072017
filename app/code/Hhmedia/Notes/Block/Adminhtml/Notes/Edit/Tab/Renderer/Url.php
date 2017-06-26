<?php
 
namespace Hhmedia\Notes\Block\Adminhtml\Notes\Edit\Tab\Renderer;
 
use Magento\Framework\DataObject;

class Url extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    public function render(DataObject $row)
    {
        $noteId = $row->getId();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl();
        $url = $baseUrl.'notes/index/view/id/'.$noteId;
        return '<a target="_blank" href="'.$url.'">'.$url.'</a>';
    }
}