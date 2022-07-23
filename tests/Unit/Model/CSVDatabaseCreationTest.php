<?php

namespace Tomdkd\ExcelDatabaseImporter\Tests\Unit\Fields;

use PHPUnit\Framework\TestCase;
use Tomdkd\ExcelDatabaseImporter\Service\FilesystemService;
use Tomdkd\ExcelDatabaseImporter\Util\SQLColumnGenerator;
use Tomdkd\ExcelDatabaseImporter\Util\SQLDatabaseGenerator;

class CSVDatabaseCreationTest extends TestCase
{
    public function testConvertRealSQLType()
    {
        $stack = [
            '52' => 'INT',
            '2.5' => 'DECIMAL',
            'hello' => 'VARCHAR(255)',
            'hhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhhh' => 'TEXT',
            '2022-01-01' => 'DATE',
            'true' => 'BOOLEAN',
        ];

        $filesystem = new FilesystemService();

        foreach ($stack as $value => $return) {
            $this->assertSame($return, $filesystem->getSQLTypeFromString($value));
        }
    }

    public function testSQLFunction() {
        $stack         = [];
        $columns       = ['janvier', 'fevrier', 'mars', 'avril', 'mai', 'juin'];
        $database_name = 'Toto';

        for ($i = 0; $i <= 6; $i++) {
            $stack[] = rand(0, 100);
        }

        $database = new SQLDatabaseGenerator($database_name);
        $stack    = [implode(',', $stack)];

        foreach ($columns as $columnIndex => $columnName) {
            $column = new SQLColumnGenerator($columnName, $columnIndex, $stack, ',');
            $this->assertSame($columnName, $column->getName());
            $this->assertIsString($column->getValue(0));
            $this->assertIsString($column->getDatatype());
            $this->assertIsBool($column->isNullable());
            $this->assertIsBool($column->isPrimary());
        }
    }
}