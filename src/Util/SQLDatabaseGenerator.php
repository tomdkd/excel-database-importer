<?php

namespace Tomdkd\ExcelDatabaseImporter\Util;

class SQLDatabaseGenerator
{

    private ?string $databaseName   = null;
    private array $tables           = [];
    private ?string $sqlQuery       = null;

    const DATABASE_MYSQL   = 'mysql';
    const DATABASE_MARIADB = 'mariadb';
    const DATABASE_POSTGRE = 'postgre';


    public function __construct(string $databaseName)
    {
        $this->databaseName   = $databaseName;
    }

    public function toSQL(): self
    {
        // Database base creation
        $this->addToQuery(sprintf("CREATE DATABASE IF NOT EXISTS %s; \n", $this->databaseName));

        foreach ($this->tables as $table) {
            // Generate tables as SQL query
            $this->addToQuery(sprintf('CREATE TABLE IF NOT EXISTS %s ', $table->getTableName()));
            $columns = [];

            foreach ($table->getColumns() as $column) {
                // Adding column structure
                $columns[] = sprintf(
                    '%s %s %s %s',
                    $column->getName(),
                    $column->getDatatype(),
                    $column->isNullable() ? 'NULLABLE' : 'NOT NULL',
                    $column->isPrimary() ? 'PRIMARY KEY' : '',
                );
            }

            $this->addToQuery(sprintf("(%s); \n", implode(', ', $columns)));
        }

        return $this;
    }

    public function save(string $path): bool
    {
        $filename = sprintf('create_%s_database.sql', $this->databaseName);
        return file_put_contents(sprintf('%s/%s', $path, $filename), $this->sqlQuery);
    }

    public function addTable(SQLTableGenerator $table): void
    {
        $this->tables[] = $table;
    }

    public function getSQL(): ?string
    {
        return $this->sqlQuery;
    }

    private function validate(): bool
    {
        if (is_null($this->databaseName) || empty($this->tables)) {
            return false;
        }
        return true;
    }

    public function addToQuery(string $text): void
    {
        $this->sqlQuery = sprintf('%s %s', $this->sqlQuery, $text);
    }
}
