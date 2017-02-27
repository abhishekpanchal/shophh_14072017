<?php

namespace Unirgy\DropshipBatch\Cron;

class ProcessStandard extends AbstractCron
{
    public function execute()
    {
        session_write_close();
        ignore_user_abort(true);
        set_time_limit(0);
        ob_implicit_flush();

        $batches = $this->_batchFactory->create()->getCollection();

        // dispatch scheduled batches
        $batches->loadScheduledBatches();
        $batches->addPendingPOsToExport(true)->exportOrders();
        $batches->addPendingStockPOsToExport(true)->exportStockpo();
        $batches->importOrders();
        $batches->importInventory();

        // generate new scheduled batches and clean batches history
        $this->_bHlp->generateSchedules();
        $this->_bHlp->cleanupSchedules();
    }
}