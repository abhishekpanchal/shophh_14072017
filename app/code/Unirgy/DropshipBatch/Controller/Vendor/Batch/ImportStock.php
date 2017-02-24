<?php

namespace Unirgy\DropshipBatch\Controller\Vendor\Batch;



class ImportStock extends AbstractBatch
{
	public function execute()
    {
        $this->_renderPage(null, 'importstock');
    }
}
