<?php
/**
 * Adminhtml magazine list block
 *
 */
namespace Hhmedia\Magazine\Block\Adminhtml;

class Magazine extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_magazine';
        $this->_blockGroup = 'Hhmedia_Magazine';
        $this->_headerText = __('Magazine');
        $this->_addButtonLabel = __('Add New Magazine');
        parent::_construct();
        if ($this->_isAllowedAction('Hhmedia_Magazine::save')) {
            $this->buttonList->update('add', 'label', __('Add New Magazine'));
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
