<?php

namespace Bluebadger\Coupon\Logger;

use Monolog\Logger as MonoLogger;

/**
 * Class Handler
 * @package Bluebadger\Coupon\Logger
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
    protected $fileName = '/var/log/bluebadger_coupon.log';
}
