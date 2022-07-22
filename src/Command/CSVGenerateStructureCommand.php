<?php

namespace Tomdkd\ExcelDatabaseImporter\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CSVGenerateStructureCommand extends Command
{

    protected static $defaultName = 'database:csv:generate-structure';

    /**
     * @inheritDoc
     *
     * @return void
     */
    protected function configure()
    {
        $this->setDescription('Try your CSV file and generate an example of your future database structure.');
        $this->addOption('validate', 'val', InputArgument::OPTIONAL, 'Only validate database structure.');
        $this->addArgument('file_path', InputArgument::REQUIRED, 'Path to the CSV file to analyze.');
        $this->addArgument('output_folder', InputArgument::OPTIONAL, 'Path to output folder.');

        parent::configure();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @inheritDoc
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return Command::SUCCESS;
    }
}
