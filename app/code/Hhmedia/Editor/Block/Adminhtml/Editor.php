<?php
/**
 * Adminhtml editor list block
 *
 */
namespace Hhmedia\Editor\Block\Adminhtml;

class Editor extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_editor';
        $this->_blockGroup = 'Hhmedia_Editor';
        $this->_headerText = __('Editor');
        $this->_addButtonLabel = __('Add New Editor');
        parent::_construct();
        if ($this->_isAllowedAction('Hhmedia_Editor::save')) {
            $this->buttonList->update('add', 'label', __('Add New Editor'));
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
