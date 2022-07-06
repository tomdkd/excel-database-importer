<?php

namespace Tomdkd\ExcelDatabaseImporter\Service;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\File\File;

class FilesystemService
{

    public function getSQLTypeFromString(string $string): ?string
    {
        $string            = trim($string);
        $intRegex          = "/[^0-9.]+/";
        $floatRegex        = "/[.]+/";
        $datetimeRegex     = "/[0-9]{4}-[0-9]{2}-[0-9]{2}/";
        $boolPossibilities = ['1', '0', 'true', 'false', 'yes', 'no'];

        if(empty($string)) return null;

        if(!preg_match($intRegex,$string)) {

            if(preg_match($floatRegex,$string)) {
                return "DECIMAL";
            }

            return "INT";
        }

        if (in_array($string, $boolPossibilities)) return 'BOOLEAN';

        if (preg_match($datetimeRegex, $string)) return 'DATE';

        if (strlen($string) > 30) return 'TEXT';

        return 'VARCHAR(255)';
    }

    public function writeInFile(string $data, string $filename, string $path): bool
    {
        return file_put_contents(sprintf('%s/%s', $path, $filename), $data);
    }

}