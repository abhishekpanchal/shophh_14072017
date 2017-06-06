<?php
/**
 * Unirgy LLC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.unirgy.com/LICENSE-M1.txt
 *
 * @category   Unirgy
 * @package    Unirgy_DropshipSplit
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipSplit\Model;

use Unirgy\DropshipSplit\Helper\Data as DropshipSplitHelperData;
use Unirgy\Dropship\Helper\Data as HelperData;
use Unirgy\Dropship\Model\Source\AbstractSource;

class Source extends AbstractSource
{
    /**
     * @var HelperData
     */
    protected $_hlp;

    /**
     * @var DropshipSplitHelperData
     */
    protected $_splitHlp;

    public function __construct(array $data = [], 
        HelperData $helperData, 
        DropshipSplitHelperData $dropshipSplitHelperData)
    {
        $this->_hlp = $helperData;
        $this->_splitHlp = $dropshipSplitHelperData;

        parent::__construct($data);
    }

    public function toOptionHash($selector=false)
    {
        $hlp = $this->_hlp;
        $hlpc = $this->_splitHlp;

        switch ($this->getPath()) {

        case 'carriers/udsplit/free_method':
            $options = [
                'total' => __('Total'),
            ];
            break;

        case 'split_shipping_methods':
            $options = [
                'test' => 'test',
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