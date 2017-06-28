<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-01-07
 * Time: 23:22
 */

namespace Bluebadger\Importer\Console\Command;

use Bluebadger\Importer\Exception\CouldNotHandleException;
use Bluebadger\Importer\Helper\Config;
use Bluebadger\Importer\Model\AttributeImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportAttributesCommand
 * @package Bluebadger\Importer\Console\Command
 */
class ImportAttributesCommand extends Command
{
    /**
     * @var AttributeImporter
     */
    protected $attributeImporter;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * ImportAttributesCommand constructor.
     * @param AttributeImporter $attributeImporter
     * @param Config $configHelper
     * @param null $name
     */
    public function __construct(
        AttributeImporter $attributeImporter,
        Config $configHelper,
        $name = null
    )
    {
        $this->attributeImporter = $attributeImporter;
        $this->configHelper = $configHelper;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('bluebadger:importer:attributes')->setDescription('Import Attributes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(-1);
        $output->writeln('Starting attribute sets import...');
        $this->attributeImporter->setCsvFilePath($this->configHelper->getFilePathAttributesCsv());

        try {
            $this->attributeImporter->process();
        } catch (CouldNotHandleException $e) {
            $output->writeln('An error occurred while processing import: ' . $e->getMessage());
        }

        $output->writeln('Import complete.');
    }
}