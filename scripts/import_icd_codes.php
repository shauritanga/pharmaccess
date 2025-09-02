<?php
// Usage: php scripts/import_icd_codes.php public/data/icd_codes.csv
// Imports ICD codes CSV into icd_codes table (columns: icd_id, icd_code, icd_name)

if ($argc < 2) {
    fwrite(STDERR, "Usage: php {$argv[0]} <path_to_icd_codes.csv>\n");
    exit(1);
}
$csvPath = $argv[1];
if (!is_readable($csvPath)) {
    fwrite(STDERR, "CSV not readable: $csvPath\n");
    exit(2);
}

// Minimal .env parser
function env_read($key, $default = null) {
    static $env = null;
    if ($env === null) {
        $env = [];
        $path = __DIR__ . '/../.env';
        if (is_readable($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if ($line === '' || $line[0] === '#') continue;
                $pos = strpos($line, '=');
                if ($pos === false) continue;
                $k = trim(substr($line, 0, $pos));
                $v = trim(substr($line, $pos + 1));
                $v = trim($v, "\"' ");
                $env[$k] = $v;
            }
        }
    }
    return $env[$key] ?? $default;
}

$host = env_read('DB_HOST', '127.0.0.1');
$port = (int)env_read('DB_PORT', '3306');
$db   = env_read('DB_DATABASE', 'pharmaccess');
$user = env_read('DB_USERNAME', 'root');
$pass = env_read('DB_PASSWORD', '');

$dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::MYSQL_ATTR_LOCAL_INFILE => true,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Throwable $e) {
    fwrite(STDERR, "DB connect failed: " . $e->getMessage() . "\n");
    exit(3);
}

$in = fopen($csvPath, 'r');
if (!$in) {
    fwrite(STDERR, "Failed to open CSV: $csvPath\n");
    exit(4);
}

// Read header
$header = fgetcsv($in, 0, ',', '"', "\\");
if ($header === false) {
    fwrite(STDERR, "CSV is empty\n");
    exit(5);
}

// Normalize header mapping
$norm = function ($s) {
    $s = preg_replace('/\xEF\xBB\xBF/', '', (string)$s); // strip BOM
    return strtolower(trim($s));
};
$map = [];
foreach ($header as $i => $h) {
    $map[$norm($h)] = $i;
}
$idxId   = $map['icdid']   ?? $map['id']   ?? null;
$idxCode = $map['icdcode'] ?? $map['code'] ?? null;
$idxName = $map['icdname'] ?? $map['name'] ?? null;
if ($idxId === null || $idxCode === null || $idxName === null) {
    fwrite(STDERR, "Header must contain ICDID, ICDCode, ICDName (found: " . implode(',', $header) . ")\n");
    exit(6);
}

// Helper: convert to UTF-8 if needed (handles Windows-1252/ISO-8859-1)
$toUtf8 = function ($s) {
    $s = (string)$s;
    if ($s === '') return $s;
    if (mb_detect_encoding($s, 'UTF-8', true)) return $s;
    $t = @iconv('Windows-1252', 'UTF-8//TRANSLIT', $s);
    if ($t === false) {
        $t = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $s);
    }
    return $t !== false ? $t : $s;
};

$pdo->exec("SET NAMES utf8mb4");
$pdo->beginTransaction();
$sql = "INSERT INTO icd_codes (icd_id, icd_code, icd_name) VALUES (:id, :code, :name)
        ON DUPLICATE KEY UPDATE icd_code=VALUES(icd_code), icd_name=VALUES(icd_name)";
$stmt = $pdo->prepare($sql);

$total = 0; $inserted = 0; $updated = 0;
while (($row = fgetcsv($in, 0, ',', '"', "\\")) !== false) {
    $total++;
    $id   = trim((string)($row[$idxId]   ?? ''));
    $code = trim((string)($row[$idxCode] ?? ''));
    $name = trim((string)($row[$idxName] ?? ''));
    if ($id === '' || $code === '' || $name === '') continue;
    $code = $toUtf8($code);
    $name = $toUtf8($name);
    $stmt->execute([':id' => (int)$id, ':code' => $code, ':name' => $name]);
    $affected = $stmt->rowCount(); // 1 = insert, 2 = update (MySQL may vary)
    if ($affected >= 2) $updated++; else $inserted++;
}
$pdo->commit();

fclose($in);

// Verify a few rows
$cnt = (int)$pdo->query('SELECT COUNT(*) AS c FROM icd_codes')->fetchColumn();
$sample = $pdo->query('SELECT icd_id, icd_code, icd_name FROM icd_codes ORDER BY icd_id ASC LIMIT 5')->fetchAll();

fwrite(STDOUT, "Done. Processed $total rows; inserted ~$inserted, updated ~$updated.\n");
fwrite(STDOUT, "icd_codes total rows: $cnt\n");
fwrite(STDOUT, "First rows:\n");
foreach ($sample as $r) {
    fwrite(STDOUT, sprintf("%s,%s,%s\n", $r['icd_id'], $r['icd_code'], $r['icd_name']));
}

