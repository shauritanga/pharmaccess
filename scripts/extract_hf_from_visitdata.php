<?php
// Usage: php scripts/extract_hf_from_visitdata.php "/path/to/VisitData(Utilization Data).csv" "/path/to/output.csv"
// Reads the VisitData CSV and extracts a unique list of HFID, HFName as CSV.

if ($argc < 3) {
    fwrite(STDERR, "Usage: php {$argv[0]} <input_csv> <output_csv>\n");
    exit(1);
}
$input = $argv[1];
$output = $argv[2];

if (!is_readable($input)) {
    fwrite(STDERR, "Input not readable: $input\n");
    exit(2);
}

$in = fopen($input, 'r');
if (!$in) {
    fwrite(STDERR, "Failed to open input: $input\n");
    exit(3);
}

// Create output dir if needed
$dir = dirname($output);
if (!is_dir($dir)) {
    if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
        fwrite(STDERR, "Failed to create directory: $dir\n");
        exit(4);
    }
}
$out = fopen($output, 'w');
if (!$out) {
    fclose($in);
    fwrite(STDERR, "Failed to open output: $output\n");
    exit(5);
}

// Write header
fputcsv($out, ['HFID', 'HFName']);

// Helper to normalize header names
$norm = function ($s) {
    $s = trim($s);
    $s = preg_replace('/\xEF\xBB\xBF/', '', $s); // remove UTF-8 BOM if present
    return strtolower(preg_replace('/[^a-z0-9]/i', '', $s));
};

$header = fgetcsv($in);
if ($header === false) {
    fwrite(STDERR, "Empty CSV: $input\n");
    fclose($in); fclose($out);
    exit(6);
}
$map = [];
foreach ($header as $i => $h) {
    $map[$norm($h)] = $i;
}

// Accept common variations
$idxHFID = $map['hfid'] ?? ($map['hfidint'] ?? null);
$idxHFName = $map['hfname'] ?? ($map['healthfacilityname'] ?? $map['hf_name'] ?? null);

if ($idxHFID === null || $idxHFName === null) {
    // Try to scan for close matches
    foreach ($map as $k => $i) {
        if ($idxHFID === null && strpos($k, 'hfid') !== false) $idxHFID = $i;
        if ($idxHFName === null && (strpos($k, 'hfname') !== false || strpos($k, 'facilityname') !== false)) $idxHFName = $i;
    }
}

if ($idxHFID === null || $idxHFName === null) {
    fwrite(STDERR, "Could not find HFID/HFName columns. Detected headers: " . implode(',', $header) . "\n");
    fclose($in); fclose($out);
    exit(7);
}

$seen = [];
$countRows = 0; $countUnique = 0;
while (($row = fgetcsv($in)) !== false) {
    $countRows++;
    $idRaw = $row[$idxHFID] ?? '';
    $nameRaw = $row[$idxHFName] ?? '';
    $id = trim($idRaw);
    $name = trim($nameRaw);
    if ($id === '' && $name === '') continue;
    if ($id === '') continue; // require ID
    // Use the first non-empty name we encounter for an ID
    if (!isset($seen[$id])) {
        $seen[$id] = $name;
        fputcsv($out, [$id, $name]);
        $countUnique++;
    } else {
        if ($seen[$id] === '' && $name !== '') {
            $seen[$id] = $name; // update internal map, but do not rewrite file
        }
    }
}

fclose($in);
fflush($out); fclose($out);

fwrite(STDOUT, "Extracted $countUnique unique facilities from $countRows rows into $output\n");

