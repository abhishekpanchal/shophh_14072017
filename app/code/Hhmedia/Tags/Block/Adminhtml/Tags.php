<?php
/**
 * Adminhtml tags list block
 *
 */
namespace Hhmedia\Tags\Block\Adminhtml;

class Tags extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_tags';
        $this->_blockGroup = 'Hhmedia_Tags';
        $this->_headerText = __('Tags');
        $this->_addButtonLabel = __('Add New Tags');
        parent::_construct();
        if ($this->_isAllowedAction('Hhmedia_Tags::save')) {
            $this->buttonList->update('add', 'label', __('Add New Tags'));
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
