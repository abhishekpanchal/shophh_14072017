<?php
/**
 * Created by PhpStorm.
 * User: lucian
 * Date: 2017-01-07
 * Time: 23:24
 */

namespace Bluebadger\Importer\Console\Command;

use Bluebadger\Importer\Exception\CouldNotHandleException;
use Bluebadger\Importer\Helper\Config;
use Bluebadger\Importer\Model\AttributeSetsImporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportAttributeSetsCommand
 * @package Bluebadger\Importer\Console\Command
 */
class ImportAttributeSetsCommand extends Command
{
    /**
     * @var AttributeSetsImporter
     */
    protected $attributeSetsImporter;

    /**
     * @var Config
     */
    protected $configHelper;

    /**
     * ImportAttributeSetsCommand constructor.
     * @param AttributeSetsImporter $attributeSetsImporter
     * @param Config $configHelper
     * @param null $name
     */
    public function __construct(
        AttributeSetsImporter $attributeSetsImporter,
        Config $configHelper,
        $name = null
    )
    {
        $this->attributeSetsImporter = $attributeSetsImporter;
        $this->configHelper = $configHelper;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('bluebadger:importer:attributesets')->setDescription('Import Attribute Sets');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        set_time_limit(-1);
        $output->writeln('Starting attribute sets import...');
        $this->attributeSetsImporter->setCsvFilePath($this->configHelper->getFilePathAttributeSetsCsv());

        try {
            $this->attributeSetsImporter->process();
        } catch (CouldNotHandleException $e) {
            $output->writeln('An error occurred while processing import: ' . $e->getMessage());
        }

        $output->writeln('Import complete.');
    }
}