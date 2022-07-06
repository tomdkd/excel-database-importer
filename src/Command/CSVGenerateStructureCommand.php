<?php

namespace Tomdkd\ExcelDatabaseImporter\Command;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\File;
use Tomdkd\ExcelDatabaseImporter\Service\FilesystemService;
use Tomdkd\ExcelDatabaseImporter\Service\SQLColumnGenerator;
use Tomdkd\ExcelDatabaseImporter\Service\SQLTableGenerator;

class CSVGenerateStructureCommand extends Command
{
    private string $projectDir;

    public function __construct(string $name = null, string $projectDir)
    {
        $this->projectDir = $projectDir;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setDescription('Try your CSV file and generate an example of your future database structure.');
        $this->addOption('validate', 'val', InputArgument::OPTIONAL, 'Only validate database structure.', 'false');

        $this->addArgument('file_path', InputArgument::REQUIRED, 'Absolute path to the CSV file to analyze.');
        $this->addArgument('output_folder', InputArgument::OPTIONAL, 'Path to output folder.');

        parent::configure();
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $interface    = new SymfonyStyle($input, $output);
        $filePath     = $input->getArgument('file_path');
        $outputFolder = $input->getArgument('output_folder');
        $title        = 'Analyzing CSV file';
        $validate     = false;

        if (!$input->getOption('validate') && is_null($outputFolder)) {
            $interface->error('You sould specify an output folder.');
            return Command::FAILURE;
        }

        if ($input->getOption('validate') == 'true') {
            $validate = true;
        }

        if (!file_exists($filePath)) {
            $filePathExploded = explode('/', $filePath);
            $fileName = end($filePathExploded);
            $interface->error(sprintf('%s not found in %s', $fileName, $filePath));

            return Command::FAILURE;
        }

        $fileInfos = new File($filePath);

        if ($fileInfos->getExtension() != 'csv') {
            $interface->error(sprintf('%s is not a CSV file.', $fileInfos->getBasename()));
            return Command::FAILURE;
        }

        $interface->title($title);

        $progress  = $interface->createProgressBar();
        $file      = file($fileInfos->getPathname());
        $csv       = new Csv();
        $tableName = 'toto';

        $progress->start();
        $csv->load($fileInfos->getPathname());

        $sheetDelimiter = $csv->getDelimiter();
        $columns        = explode($sheetDelimiter, $file[0]);
        $allValues      = [];

        unset($file[0]);

        sleep(2);

        foreach ($file as $index => $line) {
            $allValues[] = explode($sheetDelimiter, $line);
            $progress->advance();
            $progress->display();
        }

        $progress->finish();
        $interface->newLine(2);

        $interface->success(sprintf('%s columns and %s rows found.', count($columns), count($allValues)));

        sleep(2);

        $interface->title('Converting file rows into SQL queries.');
        $progress = $interface->createProgressBar(count($allValues));
        $progress->start();
        $sqlTableGenerator = new SQLTableGenerator($tableName);

        foreach ($columns as $columnIndex => $columnName) {
            $sqlTableGenerator->addColumn(new SQLColumnGenerator($columnName, $columnIndex, $allValues));
            $progress->advance();
            $progress->display();
        }

        $sqlQuery = $sqlTableGenerator->toSql();
        $progress->finish();
        $interface->newLine(2);

        if ($validate) {
            $interface->success('SQL query generated successfully !');
            $interface->newLine(2);
            $interface->info($sqlQuery);

            $interface->newLine();
            if ($interface->ask('Would like to save this query ?', 'yes') == 'no') {
                return Command::SUCCESS;
            }
        }
        else {
            $interface->title('Generating sql file with queries');
        }

        $filesystem = new FilesystemService();
        $filename = sprintf('create_table_%s.sql', $tableName);

        if (!$filesystem->writeInFile($sqlQuery,
            $filename,
            $outputFolder))
        {
            $interface->error(sprintf('Unable to write in file %s.', $filename));
            return Command::FAILURE;
        }
        else {
            $interface->success(sprintf('Successfully created %s.', $filename));
            return Command::SUCCESS;
        }
    }

}