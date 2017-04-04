<?php
/**
 * Adminhtml collection list block
 *
 */
namespace Hhmedia\Collection\Block\Adminhtml;

class Collection extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_collection';
        $this->_blockGroup = 'Hhmedia_Collection';
        $this->_headerText = __('Collection');
        $this->_addButtonLabel = __('Add New Collection');
        parent::_construct();
        if ($this->_isAllowedAction('Hhmedia_Collection::save')) {
            $this->buttonList->update('add', 'label', __('Add New Collection'));
        } else {
            $this->buttonList->remove('add');
        }
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
