<?php 

namespace Hhmedia\Override\Block;

use Magento\Framework\View\Element\Template;

class Result extends \Magento\CatalogSearch\Block\Result
{

    const PRICE_DELTA = 0.001;

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

    protected function _getProductCollection()
    {
        if (null === $this->productCollection) {
            $this->productCollection = $this->getListBlock()->getLoadedProductCollection();
        }
        $price = $this->getRequest()->getParam('price');
        if($price != ''){
            $split = explode('-',$price);
            if($split[0] == '' && $split[1] != ''){
                $from = self::PRICE_DELTA;
                $to = trim($split[1]);
            }elseif(($split[0] != '' && $split[1] == '')){
                $from = trim($split[0]);
                $to = '10000000000000000'; 
            }else{
                $from = trim($split[0]);
                $to = trim($split[1]); 
            }
            return $this->productCollection->addFieldToFilter('price',['from'=>$from,'to'=>$to]);
        }
        return $this->productCollection;
    }
}