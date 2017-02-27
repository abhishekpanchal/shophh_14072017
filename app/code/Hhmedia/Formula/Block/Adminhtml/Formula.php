<?php
/**
 * Adminhtml formula list block
 *
 */
namespace Hhmedia\Formula\Block\Adminhtml;

class Formula extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_formula';
        $this->_blockGroup = 'Hhmedia_Formula';
        $this->_headerText = __('Decorating Formulas');
        $this->_addButtonLabel = __('Add New Formula');
        parent::_construct();
        if ($this->_isAllowedAction('Hhmedia_Formula::save')) {
            $this->buttonList->update('add', 'label', __('Add New Formula'));
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
