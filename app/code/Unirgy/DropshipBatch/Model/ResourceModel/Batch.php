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
 * @package    Unirgy_DropshipBatch
 * @copyright  Copyright (c) 2008-2009 Unirgy LLC (http://www.unirgy.com)
 * @license    http:///www.unirgy.com/LICENSE-M1.txt
 */

namespace Unirgy\DropshipBatch\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Zend\Json\Json;

class Batch extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('udropship_batch', 'batch_id');
    }

    public function _afterSave(AbstractModel $object)
    {
        parent::_afterSave($object);
        $this->flushRowsLog($object);
    }

    public function flushRowsLog(AbstractModel $object)
    {
        if ($object->getRowsLog()) {
        	if (in_array($object->getBatchType(), ['import_inventory', 'export_inventory'])) {
            	$table = $this->getTable('udropship_batch_invrow');
        	} else {
        		$table = $this->getTable('udropship_batch_row');
        	}
            $id = $object->getId();
            foreach ($object->getRowsLog() as $l) {
                $l['batch_id'] = $id;
                if (is_array($l['row_json'])) {
                    $l['row_json'] = Json::encode($l['row_json']);
                }
                $this->getConnection()->insert($table, $l);
            }
            $object->unsRowsLog();
        }
    }
}