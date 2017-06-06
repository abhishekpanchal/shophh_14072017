<?php

namespace Unirgy\DropshipVendorPromotions\Model;

use Unirgy\DropshipVendorPromotions\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Source\AbstractSource;

class Source extends AbstractSource
{
    /**
     * @var HelperData
     */
    protected $_helperData;

    public function __construct(
        HelperData $helperData,
        array $data = []
    )
    {
        $this->_helperData = $helperData;

        parent::__construct($data);
    }

    const UDPROMO_STATUS_ACTIVE = 1;
    const UDPROMO_STATUS_INACTIVE = 0;

    public function toOptionHash($selector=false)
    {
        $hlp = $this->_helperData;

        $options = [];

        switch ($this->getPath()) {

            case 'statuses':
                $options = [
                    self::UDPROMO_STATUS_ACTIVE  => __('Active'),
                    self::UDPROMO_STATUS_INACTIVE => __('Inactive'),
                ];
                break;

            default:
                throw new \Exception(__('Invalid request for source options: '.$this->getPath()));
        }

        if ($selector) {
            $options = [''=>__('* Please select')] + $options;
        }

        return $options;
    }
}
