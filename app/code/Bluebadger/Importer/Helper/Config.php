<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-01-07
 * Time: 23:19
 */

namespace Bluebadger\Importer\Helper;

use Bluebadger\Importer\Logger\Logger;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\Dir\Reader;

/**
 * Class Config
 * @package Bluebadger\Importer\Helper
 */
class Config extends \Magento\Framework\App\Helper\AbstractHelper
{
    const DIRECTORY_NAME_DATA = 'data';
    const FILENAME_ATTRIBUTE_SETS_CSV= 'attributesets.csv';
    const FILENAME_UPDATE_ATTRIBUTE_SETS_CSV= 'attributesetsUpdate.csv';
    const FILENAME_ATTRIBUTES_CSV= 'attributes.csv';
    const FILENAME_UPDATE_ATTRIBUTES_CSV= 'attributesUpdate.csv';

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @var string $_dataDirectoryPath
     */
    private $_dataDirectoryPath;

    /**
     * Config constructor.
     * @param Context $context
     * @param Reader $reader
     */
    public function __construct(
        Logger $logger,
        Context $context,
        Reader $reader
    )
    {
        $this->logger = $logger;
        $this->reader = $reader;
        parent::__construct($context);
    }

    /**
     * Retrieve the data directory path.
     *
     * @return string
     */
    public function getDataDirectoryPath()
    {
        if (!$this->_dataDirectoryPath) {
            $this->_dataDirectoryPath = $this->reader->getModuleDir('', $this->_getModuleName());
            $this->_dataDirectoryPath .= DIRECTORY_SEPARATOR . self::DIRECTORY_NAME_DATA;
        }

        return $this->_dataDirectoryPath;
    }

    /**
     * Get path to file containing attribute sets to import.
     *
     * @return string
     */
    public function getFilePathAttributeSetsCsv()
    {
        return (string) $this->getDataDirectoryPath() . DIRECTORY_SEPARATOR . self::FILENAME_ATTRIBUTE_SETS_CSV;
    }

    /**
     * Get path to file containing attribute sets to import or update.
     *
     * @return string
     */
    public function getUpdateFilePathAttributeSetsCsv()
    {
        return (string) $this->getDataDirectoryPath() . DIRECTORY_SEPARATOR . self::FILENAME_UPDATE_ATTRIBUTE_SETS_CSV;
    }

    /**
     * Get path to file containing attributes to import.
     *
     * @return string
     */
    public function getFilePathAttributesCsv()
    {
        return (string) $this->getDataDirectoryPath() . DIRECTORY_SEPARATOR . self::FILENAME_ATTRIBUTES_CSV;
    }

    /**
     * Get path to file containing attributes to import or update.
     *
     * @return string
     */
    public function getUpdateFilePathAttributesCsv()
    {
        return (string) $this->getDataDirectoryPath() . DIRECTORY_SEPARATOR . self::FILENAME_UPDATE_ATTRIBUTES_CSV;
    }

    /**
     * Log messages.
     *
     * @param string $logLevel
     * @param $message
     */
    public function log(string $logLevel, $message)
    {
        $this->logger->log($logLevel, $message);
    }
}