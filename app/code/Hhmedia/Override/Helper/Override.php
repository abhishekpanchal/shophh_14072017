<?php

namespace Hhmedia\Override\Helper;


class Override extends \Magento\Framework\Url\Helper\Data
{
	protected $_productloader; 

    protected $_registry;

    protected $order;

    private $productRepository; 

	public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Sales\Model\Order $order,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $_productloader
    ){
		$this->_productloader = $_productloader;
        $this->_registry = $registry;
        $this->order = $order;
        $this->productRepository = $productRepository;
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

    public function getOrderDetails($incrementId)
    {
        $orderDetail = $this->order->loadByIncrementId($incrementId);
        return $orderDetail;
    }

    public function getProductBySku($sku)
    {
        return $this->productRepository->get($sku);
    }
    
}