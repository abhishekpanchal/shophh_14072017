<?php
/**
 * Adminhtml notes list block
 *
 */
namespace Hhmedia\Notes\Block\Adminhtml;

class Notes extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_notes';
        $this->_blockGroup = 'Hhmedia_Notes';
        $this->_headerText = __('Notes');
        $this->_addButtonLabel = __('Add New Notes');
        parent::_construct();
        if ($this->_isAllowedAction('Hhmedia_Notes::save')) {
            $this->buttonList->update('add', 'label', __('Add New Notes'));
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
