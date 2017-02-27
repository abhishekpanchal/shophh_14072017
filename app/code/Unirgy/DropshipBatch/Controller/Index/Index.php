<?php

namespace Unirgy\DropshipBatch\Controller\Index;



class Index extends AbstractIndex
{
    public function execute()
    {
        $this->_forward('index', 'vendor');
    }
}
