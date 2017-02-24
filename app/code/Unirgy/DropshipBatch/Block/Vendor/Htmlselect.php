<?php

namespace Unirgy\DropshipBatch\Block\Vendor;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Escaper;
use Magento\Store\Model\ScopeInterface;
use Unirgy\DropshipBatch\Model\Source;

class Htmlselect extends Select
{
    /**
     * @var Source
     */
    protected $_bSrc;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;

    public function __construct(Factory $factoryElement, 
        CollectionFactory $factoryCollection, 
        Escaper $escaper, 
        Source $modelSource, 
        ScopeConfigInterface $configScopeConfigInterface,
        \Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->_bSrc = $modelSource;
        $this->_scopeConfig = $configScopeConfigInterface;
        $this->_urlBuilder = $urlBuilder;

        parent::__construct($factoryElement, $factoryCollection, $escaper);
    }

    public function getValues()
    {
        $values = $this->getData('values');
        return empty($values) ? $this->_bSrc->setPath('vendors_import_orders')->toOptionArray() : $values;
    }
    protected function _getValues()
    {
        return $this->_bSrc->setPath('vendors_import_orders')->toOptionHash();
    }
    public function getNameValue()
    {
        $values = $this->_getValues();
        $value = $this->_getData('value');
        return isset($values[$value]) ? $values[$value] : $value;
    }
    public function getEscapedNameValue()
    {
        return $this->_escape($this->getNameValue());
    }
    public function getElementHtml()
    {
        if ($this->_scopeConfig->isSetFlag('udropship/vendor/autocomplete_htmlselect', ScopeInterface::SCOPE_STORE)) {
            $html = '<input id="_autocomplete_'.$this->getHtmlId().'" class="input-text" name="_autocomplete_'.$this->getName()
                .'" value="'.$this->getEscapedNameValue().'" '.$this->serialize($this->getHtmlAttributes()).'/>'."\n";
            $html .= '
            <input type="hidden" name="'.$this->getName().'" id="'.$this->getHtmlId().'" value="'.$this->getEscapedValue().'">
            <div class="autocomplete" style="font-weight:bold; display: none;" id="_autocomplete_container_'.$this->getHtmlId().'"></div>
            <script type="text/javascript">
require(["jquery", "prototype"], function(jQuery) {

            	(function () {
                	var acObserve = function(){
                    	if ($("_autocomplete_'.$this->getHtmlId().'").value=="") $("'.$this->getHtmlId().'").value = ""
                	}
                    $("_autocomplete_'.$this->getHtmlId().'").observe("change", acObserve)
                    $("_autocomplete_'.$this->getHtmlId().'").observe("click", acObserve)
                	new Ajax.Autocompleter(
                        "_autocomplete_'.$this->getHtmlId().'",
                        "_autocomplete_container_'.$this->getHtmlId().'",
                        "'.$this->_urlBuilder->getUrl('udbatch/index/vendorAutocomplete').'",
                        {
                            paramName: "vendor_name",
                            method: "get",
                            minChars: 2,
                            updateElement: function(el) {
                                $("'.$this->getHtmlId().'").value = el.title;
                                $("_autocomplete_'.$this->getHtmlId().'").value = el.innerHTML.stripTags();
                			},
                            onShow : function(element, update) {
                                if(!update.style.position || update.style.position=="absolute") {
                                    update.style.position = "absolute";
                                    Position.clone(element, update, {
                                        setHeight: false,
                                        offsetTop: element.offsetHeight
                                    });
                                }
                                Effect.Appear(update,{duration:0});
                            }

        	            }
        	        )
    	        })()
            
});
</script>
            ';
            $html.= $this->getAfterElementHtml();
        } else {
            $html = parent::getElementHtml();
        }
        return $html;
    }

    public function getHtmlAttributes()
    {
        if ($this->_scopeConfig->isSetFlag('udropship/vendor/autocomplete_htmlselect', ScopeInterface::SCOPE_STORE)) {
            return ['type', 'title', 'class', 'style', 'onclick', 'onchange', 'onkeyup', 'disabled', 'readonly', 'maxlength', 'tabindex'];
        } else {
            return parent::getHtmlAttributes();
        }
    }
}
