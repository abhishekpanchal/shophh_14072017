<?php
namespace Bg\Freshdesk\Block\Adminhtml\Createticket;
 
use Magento\Backend\Block\Widget\Form\Container;
 
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
 
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }
 
    
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Bg_Freshdesk';
        $this->_controller = 'adminhtml_createticket';
 	
        parent::_construct();
	$this->buttonList->update('save', 'label', __('Create Ticket'));
    }
 
    
 
    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
 
    
}
