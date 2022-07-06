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

    protected function configure()
    {
        $this->setDescription('Try your CSV file and generate an example of your future database structure.');
        $this->addOption('validate', 'val', InputArgument::OPTIONAL, 'Only validate database structure.');

        $this->addArgument('file_path', InputArgument::REQUIRED, 'Path to the CSV file to analyze.');
        $this->addArgument('output_folder', InputArgument::OPTIONAL, 'Path to output folder.');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $interface    = new SymfonyStyle($input, $output);
        $filePath     = $input->getArgument('file_path');
        $outputFolder = $input->getArgument('output_folder');

        if (!$input->getOption('validate') && is_null($outputFolder)) {
            $interface->error('You sould specify an output folder.');
            return Command::FAILURE;
        }

        if (!file_exists($filePath)) {
            $filePathExploded = explode('/', $filePath);
            $fileName = end($filePathExploded);
            $interface->error(sprintf('%s not found in %s', $fileName, $filePath));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

}