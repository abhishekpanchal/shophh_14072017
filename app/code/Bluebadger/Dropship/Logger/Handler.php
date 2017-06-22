<?php

namespace Bluebadger\Dropship\Logger;

use Monolog\Logger as MonoLogger;

/**
 * Class Handler
 * @package Bluebadger\Dropship\Logger
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = MonoLogger::DEBUG;

    /**
     * Log filename.
     * @var string
     */
    protected $fileName = '/var/log/bluebadger_dropship.log';
}
