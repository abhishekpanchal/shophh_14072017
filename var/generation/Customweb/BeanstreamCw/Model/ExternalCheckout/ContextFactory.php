<?php
namespace Customweb\BeanstreamCw\Model\ExternalCheckout;

/**
 * Factory class for @see \Customweb\BeanstreamCw\Model\ExternalCheckout\Context
 */
class ContextFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager = null;

    /**
     * Instance name to create
     *
     * @var string
     */
    protected $_instanceName = null;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager, $instanceName = '\\Customweb\\BeanstreamCw\\Model\\ExternalCheckout\\Context')
    {
        $this->_objectManager = $objectManager;
        $this->_instanceName = $instanceName;
    }

    /**
     * Create class instance with specified parameters
     *
     * @param array $data
     * @return \Customweb\BeanstreamCw\Model\ExternalCheckout\Context
     */
    public function create(array $data = array())
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
