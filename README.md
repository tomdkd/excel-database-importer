# Excel Database Importer
## Status
Develop [![CI](https://github.com/tomdkd/excel-database-importer/actions/workflows/basic.yml/badge.svg?branch=develop)](https://github.com/tomdkd/excel-database-importer/actions/workflows/basic.yml)
Master [![CI](https://github.com/tomdkd/excel-database-importer/actions/workflows/basic.yml/badge.svg?branch=master)](https://github.com/tomdkd/excel-database-importer/actions/workflows/basic.yml)

**Excel Database Importer** is a Symfony bundle who can be able to convert a .xlsx or .csv file into a SQL file.

## Install with Composer
```composer require tomdkd/excel-database-importer```

## CSV File structure required
You should have a specific table in your file to be able to convert it.
- **Database name**: You can give the database name into the command line
- **Table name**: It will be your file name
- **Columns**: Each column in your page
- **Datas**: Each values for each columns

## Output
You will have the choice to display the SQL query, for human validation, or you can save the entire query into a sql file. _(The file will have the name of your Database.)_

## Commands
Use the basic command from Symfony Console to generate database structure and save it into a sql file.

``` bin/console database:csv:generate-structure path/to/csv/file directory/to/save/file ```

Add option ```--validate``` to only display SQL query.

```bin/console database:csv:generate-structure path/to/csv/file directory/to/save/file --validate```

## Dependencies
**Excel Database Importer** includes the dependency [php-sqllint](https://github.com/cweiske/php-sqllint) to check sql validity. You can use it to check if the generated sql file is valid to run on your sql environment.
