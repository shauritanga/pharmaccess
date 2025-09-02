<?php
// Usage: php scripts/convert_binary_to_int.php "public/data/risk_profile.csv" "public/data/risk_profile_converted.csv"
// Converts binary values in HealthConditions, ComplicationPreviousPregnancies, and AttendHealthFacility to integers

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

// Helper to normalize header names
$norm = function ($s) {
    $s = trim($s);
    $s = preg_replace('/\xEF\xBB\xBF/', '', $s); // remove UTF-8 BOM if present
    return strtolower(preg_replace('/[^a-z0-9]/i', '', $s));
};

// Convert binary tokens (e.g., "0 0 1 0") to integer bitmask
$binToInt = function ($value) {
    $raw = trim((string)$value);
    if ($raw === '') return 0;
    // Tokenize on whitespace and non-binary chars
    $tokens = preg_split('/\s+/', preg_replace('/[^01\s]/', ' ', $raw));
    $bits = array_values(array_filter($tokens, fn($t) => $t !== '' && ($t === '0' || $t === '1')));
    if (empty($bits)) {
        return is_numeric($raw) ? (int)$raw : 0;
    }
    // Interpret left-to-right as MSB..LSB, i.e., "00000001" -> 1, "00000010" -> 2
    $binary = implode('', $bits);
    return bindec($binary);
};

$header = fgetcsv($in);

// Helper wrappers to silence CSV deprecation warnings by providing escape param
function safe_fgetcsv($h) { return fgetcsv($h, 0, ',', '"', "\\"); }
function safe_fputcsv($h, $row) { return fputcsv($h, $row, ',', '"', "\\"); }

if ($header === false) {
    fwrite(STDERR, "Empty CSV: $input\n");
    fclose($in); fclose($out);
    exit(6);
}

// Map normalized headers to indices
$map = [];
foreach ($header as $i => $h) {
    $map[$norm($h)] = $i;
}

// Find the columns we need to convert
$healthConditionsIdx = $map['healthconditions'] ?? null;
$complicationsPrevIdx = $map['complicationpreviouspregnancies'] ?? null;
$attendHealthFacilityIdx = $map['attendhealthfacility'] ?? null;

if ($healthConditionsIdx === null) {
    fwrite(STDERR, "Warning: HealthConditions column not found\n");
}
if ($complicationsPrevIdx === null) {
    fwrite(STDERR, "Warning: ComplicationPreviousPregnancies column not found\n");
}
if ($attendHealthFacilityIdx === null) {
    fwrite(STDERR, "Warning: AttendHealthFacility column not found\n");
}

// Write header to output
safe_fputcsv($out, $header);

$countRows = 0;
$countConverted = 0;

while (($row = safe_fgetcsv($in)) !== false) {
    $countRows++;

    // Convert HealthConditions if found
    if ($healthConditionsIdx !== null && isset($row[$healthConditionsIdx])) {
        $original = $row[$healthConditionsIdx];
        $converted = $binToInt($original);
        if ($original !== (string)$converted) {
            $countConverted++;
        }
        $row[$healthConditionsIdx] = $converted;
    }

    // Convert ComplicationPreviousPregnancies if found
    if ($complicationsPrevIdx !== null && isset($row[$complicationsPrevIdx])) {
        $original = $row[$complicationsPrevIdx];
        $converted = $binToInt($original);
        if ($original !== (string)$converted) {
            $countConverted++;
        }
        $row[$complicationsPrevIdx] = $converted;
    }

    // Convert AttendHealthFacility if found
    if ($attendHealthFacilityIdx !== null && isset($row[$attendHealthFacilityIdx])) {
        $original = $row[$attendHealthFacilityIdx];
        $converted = $binToInt($original);
        if ($original !== (string)$converted) {
            $countConverted++;
        }
        $row[$attendHealthFacilityIdx] = $converted;
    }

    safe_fputcsv($out, $row);
}

fwrite(STDOUT, "Processed $countRows rows, converted $countConverted binary values to integers\n");
fwrite(STDOUT, "Output saved to: $output\n");

fclose($in);
fflush($out); fclose($out);

fwrite(STDOUT, "Processed $countRows rows, converted $countConverted binary values to integers\n");
fwrite(STDOUT, "Output saved to: $output\n");
