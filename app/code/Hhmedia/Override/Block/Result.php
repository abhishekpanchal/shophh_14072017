<?php 

namespace Hhmedia\Override\Block;

use Magento\Framework\View\Element\Template;

class Result extends \Magento\CatalogSearch\Block\Result
{
	protected function _prepareLayout()
    {
        $title = "Search Results";
        $this->pageConfig->getTitle()->set($title);
        $q = $this->getRequest()->getParam('q');
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        $count = $this->getResultCount();
        if($count > 0){
            $text = "Showing ".$this->getResultCount()." Results for '".$q."'"; 
        }else{
            $text = "There are no results for '".$q."'"; 
        }
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'search',
                ['label' => $text, 'title' => $text]
            );
        }
        return $this;
    }
}