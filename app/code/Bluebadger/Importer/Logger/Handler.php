<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-01-07
 * Time: 23:17
 */

namespace Bluebadger\Importer\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger as MonoLogger;

/**
 * Class Handler
 * @package Bluebadger\Importer\Logger
 */
class Handler extends Base
{
    const FILE_PATH_LOG = '/var/log/bluebadger_importer.log';

    /**
     * Logging level
     * @var int
     */
    protected $loggerType = MonoLogger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = self::FILE_PATH_LOG;
}