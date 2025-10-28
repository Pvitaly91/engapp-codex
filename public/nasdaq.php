<?php
/**
 * Script to update the description field in outpu.csv based on symbol lookups
 * from 1.csv, 2.csv, and 3.csv.
 */

$baseDir = __DIR__;
$outputFile = $baseDir . DIRECTORY_SEPARATOR . 'outpu.csv';
$sourceFiles = [
    $baseDir . DIRECTORY_SEPARATOR . '1.csv',
    $baseDir . DIRECTORY_SEPARATOR . '2.csv',
    $baseDir . DIRECTORY_SEPARATOR . '3.csv',
];

if (!file_exists($outputFile)) {
    fwrite(STDERR, "Output file not found: {$outputFile}\n");
    exit(1);
}

/**
 * Wrapper around fgetcsv with explicit parameters to avoid deprecation warnings.
 */
function readCsvRow($handle)
{
    return fgetcsv($handle, 0, ',', '"', '\\');
}

/**
 * Wrapper around fputcsv with explicit parameters to avoid deprecation warnings.
 */
function writeCsvRow($handle, array $row)
{
    return fputcsv($handle, $row, ',', '"', '\\');
}

$symbolToName = [];

foreach ($sourceFiles as $filePath) {
    if (!file_exists($filePath)) {
        continue;
    }

    $handle = fopen($filePath, 'r');
    if ($handle === false) {
        continue;
    }

    $header = readCsvRow($handle);
    if ($header === false) {
        fclose($handle);
        continue;
    }

    $indexMap = [];
    foreach ($header as $index => $columnName) {
        $normalized = strtolower(trim($columnName));
        $indexMap[$normalized] = $index;
    }

    while (($row = readCsvRow($handle)) !== false) {
        if ($row === [null] || $row === false) {
            continue;
        }

        $symbol = null;
        $name = null;

        foreach (['nasdaq symbol', 'act symbol', 'symbol'] as $key) {
            if (isset($indexMap[$key])) {
                $value = isset($row[$indexMap[$key]]) ? trim($row[$indexMap[$key]]) : '';
                if ($value !== '') {
                    $symbol = $value;
                    break;
                }
            }
        }

        if ($symbol === null || $symbol === '') {
            continue;
        }

        foreach (['security name', 'company name', 'name'] as $key) {
            if (isset($indexMap[$key])) {
                $value = isset($row[$indexMap[$key]]) ? trim($row[$indexMap[$key]]) : '';
                if ($value !== '') {
                    $name = $value;
                    break;
                }
            }
        }

        if ($name === null || $name === '') {
            continue;
        }

        if (!isset($symbolToName[$symbol])) {
            $symbolToName[$symbol] = $name;
        }
    }

    fclose($handle);
}

$handle = fopen($outputFile, 'r');
if ($handle === false) {
    fwrite(STDERR, "Unable to open output file for reading: {$outputFile}\n");
    exit(1);
}

$rows = [];
$header = readCsvRow($handle);
if ($header === false) {
    fclose($handle);
    fwrite(STDERR, "Output file appears to be empty: {$outputFile}\n");
    exit(1);
}

$rows[] = $header;
$symbolIndex = null;
$descriptionIndex = null;

foreach ($header as $index => $columnName) {
    $normalized = strtolower(trim($columnName));
    if ($normalized === 'symbol') {
        $symbolIndex = $index;
    } elseif ($normalized === 'description') {
        $descriptionIndex = $index;
    }
}

if ($symbolIndex === null || $descriptionIndex === null) {
    fclose($handle);
    fwrite(STDERR, "Output file must contain 'symbol' and 'description' columns.\n");
    exit(1);
}

while (($row = readCsvRow($handle)) !== false) {
    if ($row === [null]) {
        continue;
    }

    $symbol = isset($row[$symbolIndex]) ? trim($row[$symbolIndex]) : '';
    if ($symbol !== '' && isset($symbolToName[$symbol])) {
        $row[$descriptionIndex] = $symbolToName[$symbol];
    }

    $rows[] = $row;
}

fclose($handle);

$handle = fopen($outputFile, 'w');
if ($handle === false) {
    fwrite(STDERR, "Unable to open output file for writing: {$outputFile}\n");
    exit(1);
}

foreach ($rows as $row) {
    writeCsvRow($handle, $row);
}

fclose($handle);

echo "Descriptions updated successfully.\n";
