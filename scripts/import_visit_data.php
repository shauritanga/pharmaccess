<?php
// Usage: php scripts/import_visit_data.php public/data/visit_data.csv
// Imports visit_data.csv into the visit_data table with upsert

if ($argc < 2) {
    fwrite(STDERR, "Usage: php {$argv[0]} <path_to_visit_data.csv>\n");
    exit(1);
}
$csvPath = $argv[1];
if (!is_readable($csvPath)) {
    fwrite(STDERR, "CSV not readable: $csvPath\n");
    exit(2);
}

// Read .env (minimal)
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
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (Throwable $e) {
    fwrite(STDERR, "DB connect failed: " . $e->getMessage() . "\n");
    exit(3);
}

// Helpers
$norm = function ($s) {
    $s = preg_replace('/\xEF\xBB\xBF/', '', (string)$s);
    return strtolower(preg_replace('/[^a-z0-9]/i', '', $s));
};
$nullIf = function ($s) {
    $s = trim((string)$s);
    if ($s === '' || strcasecmp($s, 'null') === 0) return null;
    return $s;
};
$toInt = function ($s) use ($nullIf) {
    $v = $nullIf($s);
    return $v === null ? null : (int)$v;
};
$toFloat = function ($s) use ($nullIf) {
    $v = $nullIf($s);
    return $v === null ? null : (float)$v;
};
$toDate = function ($s) use ($nullIf) {
    $v = $nullIf($s);
    if ($v === null) return null;
    // Accept formats like 10/7/2020 0:00 or 10/07/2020 or 2020-10-07
    $v = trim($v);
    // Drop time if present
    $datePart = preg_split('/\s+/', $v)[0] ?? $v;
    $ts = DateTime::createFromFormat('n/j/Y', $datePart) ?: DateTime::createFromFormat('m/d/Y', $datePart) ?: DateTime::createFromFormat('Y-m-d', $datePart);
    if ($ts === false) return null;
    return $ts->format('Y-m-d');
};

$in = fopen($csvPath, 'r');
if (!$in) {
    fwrite(STDERR, "Failed to open CSV: $csvPath\n");
    exit(4);
}
$header = fgetcsv($in, 0, ',', '"', "\\");
if ($header === false) {
    fwrite(STDERR, "CSV is empty\n");
    exit(5);
}
$map = [];
foreach ($header as $i => $h) { $map[$norm($h)] = $i; }

$req = ['claimid','insureeid','familyid','gender','datefrom','dateto','icdid','hfid','hfname','claimadminid','icdid1','icdid2','icdid3','icdid4','visittype','visittype2','visittype3','dob','districtname','shehia','matibabuid','ppiscore'];
foreach ($req as $key) { if (!array_key_exists($key, $map)) { fwrite(STDERR, "Missing column: $key\n"); } }

$sql = "INSERT INTO visit_data (
  claim_id, insuree_id, family_id, matibabu_id, ppi_score, gender, date_from, date_to,
  icd_id, hf_id, hf_name, claim_admin_id, icd_id_1, icd_id_2, icd_id_3, icd_id_4,
  visit_type, visit_type_2, visit_type_3, dob, district_name, shehia
) VALUES (
  :claim_id, :insuree_id, :family_id, :matibabu_id, :ppi_score, :gender, :date_from, :date_to,
  :icd_id, :hf_id, :hf_name, :claim_admin_id, :icd_id_1, :icd_id_2, :icd_id_3, :icd_id_4,
  :visit_type, :visit_type_2, :visit_type_3, :dob, :district_name, :shehia
) ON DUPLICATE KEY UPDATE
  insuree_id=VALUES(insuree_id), family_id=VALUES(family_id), matibabu_id=VALUES(matibabu_id), ppi_score=VALUES(ppi_score),
  gender=VALUES(gender), date_from=VALUES(date_from), date_to=VALUES(date_to), icd_id=VALUES(icd_id), hf_id=VALUES(hf_id),
  hf_name=VALUES(hf_name), claim_admin_id=VALUES(claim_admin_id), icd_id_1=VALUES(icd_id_1), icd_id_2=VALUES(icd_id_2),
  icd_id_3=VALUES(icd_id_3), icd_id_4=VALUES(icd_id_4), visit_type=VALUES(visit_type), visit_type_2=VALUES(visit_type_2),
  visit_type_3=VALUES(visit_type_3), dob=VALUES(dob), district_name=VALUES(district_name), shehia=VALUES(shehia)";
$stmt = $pdo->prepare($sql);

$pdo->exec('SET NAMES utf8mb4');
$pdo->beginTransaction();

$batch = 0; $total = 0; $insertedOrUpdated = 0;
while (($row = fgetcsv($in, 0, ',', '"', "\\")) !== false) {
    $total++;
    $params = [
        ':claim_id'      => $toInt($row[$map['claimid']] ?? null),
        ':insuree_id'    => $toInt($row[$map['insureeid']] ?? null),
        ':family_id'     => $toInt($row[$map['familyid']] ?? null),
        ':matibabu_id'   => $nullIf($row[$map['matibabuid']] ?? null),
        ':ppi_score'     => $toFloat($row[$map['ppiscore']] ?? null),
        ':gender'        => $nullIf($row[$map['gender']] ?? null),
        ':date_from'     => $toDate($row[$map['datefrom']] ?? null),
        ':date_to'       => $toDate($row[$map['dateto']] ?? null),
        ':icd_id'        => $toInt($row[$map['icdid']] ?? null),
        ':hf_id'         => $toInt($row[$map['hfid']] ?? null),
        ':hf_name'       => $nullIf($row[$map['hfname']] ?? null),
        ':claim_admin_id'=> $toInt($row[$map['claimadminid']] ?? null),
        ':icd_id_1'      => $toInt($row[$map['icdid1']] ?? null),
        ':icd_id_2'      => $toInt($row[$map['icdid2']] ?? null),
        ':icd_id_3'      => $toInt($row[$map['icdid3']] ?? null),
        ':icd_id_4'      => $toInt($row[$map['icdid4']] ?? null),
        ':visit_type'    => $nullIf($row[$map['visittype']] ?? null),
        ':visit_type_2'  => $nullIf($row[$map['visittype2']] ?? null),
        ':visit_type_3'  => $nullIf($row[$map['visittype3']] ?? null),
        ':dob'           => $toDate($row[$map['dob']] ?? null),
        ':district_name' => $nullIf($row[$map['districtname']] ?? null),
        ':shehia'        => $nullIf($row[$map['shehia']] ?? null),
    ];
    // Require claim_id
    if ($params[':claim_id'] === null) continue;
    $stmt->execute($params);
    $insertedOrUpdated += $stmt->rowCount();

    // Commit every 5000 rows to keep transaction manageable
    $batch++;
    if ($batch >= 5000) {
        $pdo->commit();
        $pdo->beginTransaction();
        $batch = 0;
        fwrite(STDOUT, "."); // progress
    }
}
$pdo->commit();
fclose($in);

$cnt = (int)$pdo->query('SELECT COUNT(*) FROM visit_data')->fetchColumn();

fwrite(STDOUT, "\nDone. Processed $total rows; affected ~$insertedOrUpdated rows.\n");
fwrite(STDOUT, "visit_data total rows: $cnt\n");
$sample = $pdo->query('SELECT claim_id, insuree_id, icd_id, hf_id, hf_name, date_from, dob FROM visit_data ORDER BY claim_id ASC LIMIT 5')->fetchAll();
foreach ($sample as $r) {
    fwrite(STDOUT, sprintf("%s,%s,%s,%s,%s,%s,%s\n",
        $r['claim_id'],$r['insuree_id'],$r['icd_id'],$r['hf_id'],$r['hf_name'],$r['date_from'],$r['dob']));
}

