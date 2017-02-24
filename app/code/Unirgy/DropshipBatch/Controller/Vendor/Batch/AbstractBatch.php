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

namespace Unirgy\DropshipBatch\Controller\Vendor\Batch;

use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Unirgy\Dropship\Controller\VendorAbstract;

abstract class AbstractBatch extends VendorAbstract
{
	protected function _getSession()
    {
        return ObjectManager::getInstance()->get('Unirgy\Dropship\Model\Session');
    }
    public function getVendorShipmentCollection()
    {
        return $this->_hlp->getVendorShipmentCollection();
    }
    public function getVendorPoCollection()
    {
        return $this->_hlp->udpoHlp()->getVendorPoCollection();
    }
    public function getVendorPoShipmentCollection()
    {
        return $this->_hlp->udpoHlp()->getVendorShipmentCollection();
    }

    public function isAllowedField($field)
    {
    	return in_array($field, $this->getAllowedFields());
    }
    public function getAllowedFields()
    {
    	return ['sku', 'vendor_sku', 'stock_qty', 'stock_qty_add', 'stock_status'];
    }
}