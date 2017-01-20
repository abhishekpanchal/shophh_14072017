<?php

namespace Altima\Lookbookslider\Controller\Index;

class View extends \Magento\Framework\App\Action\Action
{

    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

}
?>