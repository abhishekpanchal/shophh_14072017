<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-01-07
 * Time: 23:27
 */

namespace Bluebadger\Importer\Api\Data;


/**
 * Interface ImporterInterface
 * @package Bluebadger\Importer\Api\Data
 */
interface ImporterInterface
{
    /**
     * Set the CSV file path.
     *
     * @param $csvFilePath
     * @return mixed
     */
    public function setCsvFilePath($csvFilePath);

    /**
     * Read each line of the CSV file and call a handler function to process it.
     *
     * @return $this;
     */
    public function process();

    /**
     * Stuff to process after.
     *
     * @return mixed
     */
    public function processAfter();

    /**
     * Log import errors.
     *
     * @param string $logLevel
     * @param $message
     * @return mixed
     */
    public function log(string $logLevel, $message);
}