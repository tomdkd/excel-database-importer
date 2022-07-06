<?php

namespace Tomdkd\ExcelDatabaseImporter\Service;

class SQLTableGenerator {

    private string $tableName;
    private array  $columns;

    public function __construct(string $tableName)
    {
        $this->tableName = strtolower($tableName);
    }

    public function toSql(): string
    {
        $columns = [];
        $values  = [];
        $valueNb = 0;

        foreach ($this->columns as $column) {
            $valueNb = count($column->getValues());

            $columns[] = sprintf('%s %s %s %s',
                $column->getName(),
                $column->getDatatype(),
                $column->isPrimary() ? 'PRIMARY KEY' : '',
                $column->isNullable() ? 'NULLABLE' : 'NOT NULL');;
        }

        for ($index = 0; $index <= ($valueNb - 1); $index++) {
            $lineValues = [];

            foreach ($this->columns as $column) {
                $lineValues[] = $column->getValue($index);
            }

            $values[] = sprintf("(%s)", implode(', ', $lineValues));
        }

        $columns = implode(", \n", $columns);
        $values  = implode(", \n", $values);

        return sprintf("CREATE TABLE %s IF NOT EXISTS \n(%s);\n\nINSERT INTO %s VALUES \n%s",
            $this->tableName,
            $columns,
            $this->tableName,
            $values);
    }

    public function addColumn(SQLColumnGenerator $column): void
    {
        $this->columns[] = $column;
    }

}