<?php

namespace Tomdkd\ExcelDatabaseImporter\Util;

class SQLTableGenerator {

    private string $tableName;
    private array  $columns;

    public function __construct(string $tableName)
    {
        $this->tableName = strtolower($tableName);
    }

    public function addColumn(SQLColumnGenerator $column): void
    {
        $this->columns[] = $column;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

}