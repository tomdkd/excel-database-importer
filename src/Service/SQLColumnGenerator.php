<?php

namespace Tomdkd\ExcelDatabaseImporter\Service;

use JetBrains\PhpStorm\NoReturn;
use Tomdkd\ExcelDatabaseImporter\Exception\EmptyColumnException;

class SQLColumnGenerator {

    private string $columnName;
    private int    $columnIndex;
    private array  $lines;
    private array  $columnValues = [];
    private array  $columnConfiguration = [];

    private FilesystemService $filesystem;

    public function __construct(string $columnName, int $columnIndex, array $values)
    {
        $this->columnName  = str_replace("\r\n", '',$columnName);
        $this->columnIndex = $columnIndex;
        $this->lines       = array_values($values);
        $this->filesystem  = new FilesystemService();

        $this->parse();
    }

    private function parse(): void
    {
        $this->getValuesOnlyForColumns();

        $this->columnConfiguration = [
            'name'     => $this->columnName,
            'datatype' => $this->getColumnType(),
            'values'   => $this->columnValues
        ];
    }

    private function getValuesOnlyForColumns(): void
    {
        foreach ($this->lines as $line) {
            $this->columnValues[] = empty($line) ? null : str_replace("\r\n", '', $line[$this->columnIndex]);
        }
    }

    public function isNullable(): bool
    {
        foreach ($this->columnValues as $value) {
            if (is_null($value)) return true;
        }

        return false;
    }

    private function getColumnType(): ?string
    {
        foreach ($this->columnValues as $value) {
            if (!is_null($value)) return $this->filesystem->getSQLTypeFromString($value);
        }

        throw new EmptyColumnException(sprintf('Column %s have only empty values', $this->columnName));
    }

    public function getName(): string {
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