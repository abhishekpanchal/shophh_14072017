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
 * @package    Unirgy_Dropship
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipBatch\Block\Adminhtml;

use Magento\Backend\Block\Widget\Grid\Container;

class Dist extends Container
{

    public function _construct()
    {
        $this->_blockGroup = 'Unirgy_DropshipBatch';
        $this->_controller = 'adminhtml_dist';
        $this->_headerText = __('Batch Import/Export History');
        parent::_construct();
        $this->removeButton('add');
    }

}
