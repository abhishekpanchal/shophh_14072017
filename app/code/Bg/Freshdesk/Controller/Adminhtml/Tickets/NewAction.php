<?php

namespace Bg\Freshdesk\Controller\Adminhtml\Tickets;

class NewAction extends \Bg\Freshdesk\Controller\Adminhtml\Tickets
{
    public function execute()
    {
        $resultForward = $this->_resultForwardFactory->create();

        return $resultForward->forward('edit');
    }
}
