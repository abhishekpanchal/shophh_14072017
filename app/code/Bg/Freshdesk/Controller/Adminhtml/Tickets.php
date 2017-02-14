<?php

namespace Bg\Freshdesk\Controller\Adminhtml;

abstract class Tickets extends \Bg\Freshdesk\Controller\Adminhtml\AbstractAction
{
    const PARAM_CRUD_ID = 'ticket_id';

    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Bg_Freshdesk::tickets_tickets');
    }

    
    protected function _getBackResultRedirect(\Magento\Framework\Controller\Result\Redirect $resultRedirect, $paramCrudId = null)
    {
        switch ($this->getRequest()->getParam('back')) {
            case 'edit':
                $resultRedirect->setPath(
                    '*/*/edit',
                    [
                        static::PARAM_CRUD_ID => $paramCrudId,
                        '_current' => true,
                        'store' => $this->getRequest()->getParam('store'),
                        'saveandclose' => $this->getRequest()->getParam('saveandclose'),
                    ]
                );
                break;
            case 'new':
                $resultRedirect->setPath('*/*/new', ['_current' => true]);
                break;
            default:
                $resultRedirect->setPath('*/*/');
        }

        return $resultRedirect;
    }



}
