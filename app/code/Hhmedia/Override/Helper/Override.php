<?php

namespace Hhmedia\Override\Helper;


class Override extends \Magento\Framework\Url\Helper\Data
{
	protected $_productloader; 

	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $_productloader
    ){
		$this->_productloader = $_productloader;
        parent::__construct($context);
    }

    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }	

}