<?php

namespace Hhmedia\Override\Helper;


class Override extends \Magento\Framework\Url\Helper\Data
{
	protected $_productloader; 

    protected $_registry;

	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $_productloader
    ){
		$this->_productloader = $_productloader;
        $this->_registry = $registry;
        parent::__construct($context);
    }

    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }	

    public function getCurrentCategory()
    {        
        return $this->_registry->registry('current_category');
    }
    
}