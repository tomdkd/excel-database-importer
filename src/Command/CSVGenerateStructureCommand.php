<?php

namespace Tomdkd\ExcelDatabaseImporter\Command;

use PhpOffice\PhpSpreadsheet\Reader\Csv;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Tomdkd\ExcelDatabaseImporter\Service\FilesystemService;
use Tomdkd\ExcelDatabaseImporter\Util\SQLColumnGenerator;
use Tomdkd\ExcelDatabaseImporter\Util\SQLDatabaseGenerator;
use Tomdkd\ExcelDatabaseImporter\Util\SQLTableGenerator;

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
        $this->addOption('validate', '', InputOption::VALUE_NONE, 'Only validate database structure.');

        $this->addArgument('file_path', InputArgument::REQUIRED, 'Absolute path to the CSV file to analyze.');
        $this->addArgument('output_folder', InputArgument::REQUIRED, 'Path to output folder.');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $interface    = new SymfonyStyle($input, $output); // Used to display errors and success
        $filesystem    = new FilesystemService($interface); // Used to have information about file
        $filePath      = $input->getArgument('file_path');
        $outputFolder = $input->getArgument('output_folder');
        $validate     = $input->getOption('validate'); // Option to only display sql query without saving
        $tableName    = "Toto";

        // Display an error on interface if file does not exist or extension is wrong
        $filesystem->setFile($filePath)->check('csv');

        $file        = $filesystem->getFileAsArray();
        $csv        = new Csv($filePath);
        $sheetInfos = [
            'sheet_name'    => $filesystem->getName(),
            'encoding'      => $csv->getInputEncoding(),
            'delimiter'     => $csv->getDelimiter() ?: ',',
            'database_name' => $filesystem->convertSheetNameIntoDatabaseName()
        ];
        $database   = new SQLDatabaseGenerator($sheetInfos['database_name']);
        $columns    = $filesystem->getColumns($file, $sheetInfos['delimiter']);

        // Processing to concatenate values for add columns
        $table = new SQLTableGenerator($tableName);

        foreach ($columns as $columnIndex => $columnName) {
            $column = new SQLColumnGenerator($columnName, $columnIndex, $file, $sheetInfos['delimiter']);
            $values = [];

            $table->addColumn($column);
        }

        $database->addTable($table);
        $database->toSQL();
        dd($database->getSQL());
    }

}