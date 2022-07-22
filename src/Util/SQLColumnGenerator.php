<?php

namespace Tomdkd\ExcelDatabaseImporter\Util;

use Tomdkd\ExcelDatabaseImporter\Exception\EmptyColumnException;
use Tomdkd\ExcelDatabaseImporter\Service\FilesystemService;

class SQLColumnGenerator
{

    private string $columnName;
    private int    $columnIndex;
    private array  $lines;
    private array  $columnValues = [];
    private array  $columnConfiguration = [];
    private string $delimiter;

    private FilesystemService $filesystem;

    public function __construct(string $columnName, int $columnIndex, array $values, string $delimiter)
    {
        $this->columnName  = str_replace("\r\n", '', $columnName);
        $this->columnIndex = $columnIndex;
        $this->lines       = array_values($values);
        $this->filesystem   = new FilesystemService();
        $this->delimiter   = $delimiter;

        $this->parse();
    }

    private function parse(): void
    {
        $this->getValuesOnlyForColumns();
        $this->columnConfiguration = [
            'name'     => $this->columnName,
            'datatype' => $this->getColumnType(),
            'values'   => implode(',', $this->columnValues)
        ];
        $this->resetConfig();
    }

    private function resetConfig(): void
    {
        $this->lines = [];
        $this->columnValues = [];
    }

    private function getValuesOnlyForColumns(): void
    {
        foreach ($this->lines as $line) {
            $line = explode($this->delimiter, $line);
            $this->columnValues[] = empty($line) ? null : $line[$this->columnIndex];
        }
    }

    public function isNullable(): bool
    {
        foreach ($this->columnValues as $value) {
            if (is_null($value)) {
                return true;
            }
        }

        return false;
    }

    private function getColumnType(): ?string
    {
        foreach ($this->columnValues as $value) {
            if (!is_null($value)) {
                return $this->filesystem->getSQLTypeFromString($value);
            }
        }

        throw new EmptyColumnException(sprintf('Column %s have only empty values', $this->columnName));
    }

    public function getName(): string
    {
        return $this->columnConfiguration['name'];
    }

    public function getDatatype(): string
    {
        return $this->columnConfiguration['datatype'];
    }

    public function getValues(): array
    {
        return $this->columnConfiguration['values'];
    }

    public function getValue(int $index): mixed
    {
        return $this->columnValues[$index];
    }

    public function isPrimary(): bool
    {
        return $this->columnIndex === 0;
    }
}
