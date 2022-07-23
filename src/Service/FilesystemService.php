<?php

namespace Tomdkd\ExcelDatabaseImporter\Service;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\File;

class FilesystemService
{
    private ?SymfonyStyle $interface;
    private File $file;

    const CHAR_TO_REPLACE  = ['-', 'é', 'è', 'à', "\r\n"];
    const CHAR_REPLACED_BY = ['_', 'e', 'e', 'a', ''];

    public function __construct(SymfonyStyle $interface = null)
    {
        $this->interface = $interface;
    }

    public function getSQLTypeFromString(string $string): ?string
    {
        $string            = trim($string);
        $intRegex          = "/[^0-9.]+/";
        $floatRegex        = "/[.]+/";
        $datetimeRegex     = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
        $boolPossibilities = ['1', '0', 'true', 'false', 'yes', 'no'];

        if (empty($string)) {
            return null;
        }

        if (!preg_match($intRegex, $string)) {
            if (preg_match($floatRegex, $string)) {
                return "DECIMAL";
            }

            return "INT";
        }

        if (in_array($string, $boolPossibilities)) {
            return 'BOOLEAN';
        }

        if (preg_match($datetimeRegex, $string)) {
            return 'DATE';
        }

        if (strlen($string) > 30) {
            return 'TEXT';
        }

        return 'VARCHAR(255)';
    }

    public function check(string $fileType): void
    {
        if ($this->file->getExtension() != $fileType) {
            $this->interface->error(sprintf('File is not a %s file.', $fileType));
            exit();
        }
    }

    public function setFile(string $filePath): self
    {
        if (!file_exists($filePath)) {
            $this->interface->error('File not found');
            exit();
        }

        $this->file = new File($filePath);

        return $this;
    }

    public function getFileAsArray(): array
    {
        return array_map(function ($line) {
            return $this->replaceCharacters(strtolower($line));
        }, file($this->file->getPathname()));
    }

    public function getName(): string
    {
        return strtolower($this->replaceCharacters($this->file->getBasename()));
    }

    public function convertSheetNameIntoDatabaseName(): string
    {
        $sheetNameExploded = explode('.', $this->getName());
        return $this->replaceCharacters(strtolower($sheetNameExploded[0]));
    }

    public function getColumns(array &$fileAsArray, string $delimiter): array
    {
        $columns = $fileAsArray[0];
        unset($fileAsArray[0]);
        $fileAsArray = array_values($fileAsArray);
        return explode($delimiter, $columns);
    }

    public function replaceCharacters(string $string): string
    {
        return str_replace(self::CHAR_TO_REPLACE, self::CHAR_REPLACED_BY, $string);
    }
}
