<?php

namespace Unirgy\DropshipBatch\Controller\Vendor\Batch;



class ImportOrders extends AbstractBatch
{
	public function execute()
    {
        $this->_renderPage(null, 'importorders');
    }
}
