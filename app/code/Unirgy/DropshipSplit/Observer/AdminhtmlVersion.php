<?php

namespace Unirgy\DropshipSplit\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Unirgy\Dropship\Helper\Data as HelperData;

class AdminhtmlVersion extends AbstractObserver implements ObserverInterface
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    public function __construct(HelperData $helperData)
    {
        $this->_helperData = $helperData;

    }

    public function execute(Observer $observer)
    {
        $this->_helperData->addAdminhtmlVersion('Unirgy_DropshipSplit');
    }
}
