<?php
// Usage: php scripts/import_services.php public/data/services.csv
// Imports services.csv into services table (columns: claim_service_id, claim_id, service_id, service_name, qty_provided)

if ($argc < 2) { fwrite(STDERR, "Usage: php {$argv[0]} <services.csv>\n"); exit(1);}
$csvPath = $argv[1];
if (!is_readable($csvPath)) { fwrite(STDERR, "CSV not readable: $csvPath\n"); exit(2);}

function env_read($key, $default = null) {
    static $env=null; if ($env===null){ $env=[]; $path=__DIR__.'/../.env'; if(is_readable($path)){ foreach(file($path, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES) as $line){ if($line===''||$line[0]==='#') continue; $pos=strpos($line,'='); if($pos===false) continue; $k=trim(substr($line,0,$pos)); $v=trim(substr($line,$pos+1)); $v=trim($v,"\"' "); $env[$k]=$v; } } } return $env[$key]??$default; }

$host=env_read('DB_HOST','127.0.0.1'); $port=(int)env_read('DB_PORT','3306'); $db=env_read('DB_DATABASE','pharmaccess'); $user=env_read('DB_USERNAME','root'); $pass=env_read('DB_PASSWORD','');
$dsn="mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";
$pdo=new PDO($dsn,$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
$pdo->exec('SET NAMES utf8mb4');

$in=fopen($csvPath,'r'); if(!$in){ fwrite(STDERR,"Failed to open CSV\n"); exit(3);}
$header=fgetcsv($in,0,',','"','\\'); if($header===false){ fwrite(STDERR,"Empty CSV\n"); exit(4);}
$norm=function($s){ return strtolower(preg_replace('/[^a-z0-9]/i','', preg_replace('/\xEF\xBB\xBF/','',(string)$s))); };
$map=[]; foreach($header as $i=>$h){ $map[$norm($h)]=$i; }

$idxClaimService=$map['claimserviceid']??$map['id']??null;
$idxClaim=$map['claimid']??null;
$idxService=$map['serviceid']??null;
$idxName=$map['servname']??$map['servicename']??null;
$idxQty=$map['qtyprovided']??$map['quantity']??null;
if($idxClaimService===null||$idxClaim===null||$idxService===null||$idxName===null||$idxQty===null){
  fwrite(STDERR,"Missing required headers. Found: ".implode(',', $header)."\n"); exit(5);
}

// Helper: ensure UTF-8 strings (handles Windows-1252/ISO-8859-1 chars like en dash, smart quotes)
$toUtf8 = function ($s) {
    $s = (string)$s; if ($s==='') return $s;
    if (mb_detect_encoding($s, 'UTF-8', true)) return $s;
    $t = @iconv('Windows-1252', 'UTF-8//TRANSLIT', $s);
    if ($t === false) { $t = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $s); }
    return $t !== false ? $t : $s;
};

$sql="INSERT INTO services (claim_service_id, claim_id, service_id, service_name, qty_provided) VALUES (:cid,:cl,:sv,:nm,:qt) ON DUPLICATE KEY UPDATE claim_id=VALUES(claim_id), service_id=VALUES(service_id), service_name=VALUES(service_name), qty_provided=VALUES(qty_provided)";
$stmt=$pdo->prepare($sql);
$exists=$pdo->prepare('SELECT 1 FROM visit_data WHERE claim_id=? LIMIT 1');

$pdo->beginTransaction();
$total=0; $affected=0; $batch=0; $skipped=0; $skippedExamples=[];
while(($row=fgetcsv($in,0,',','"','\\'))!==false){
  $total++;
  $claimServiceId=(int)trim((string)($row[$idxClaimService]??''));
  $claimId=(int)trim((string)($row[$idxClaim]??''));
  $serviceId=(int)trim((string)($row[$idxService]??''));
  $name=$toUtf8(trim((string)($row[$idxName]??'')));
  $qty=(int)trim((string)($row[$idxQty]??''));
  if($claimServiceId===0){ continue; }
  $exists->execute([$claimId]);
  if($exists->fetchColumn() === false){ $skipped++; if(count($skippedExamples)<5){$skippedExamples[]=$claimId;} continue; }
  $stmt->execute([':cid'=>$claimServiceId, ':cl'=>$claimId, ':sv'=>$serviceId, ':nm'=>$name, ':qt'=>$qty]);
  $affected += $stmt->rowCount();
  if(++$batch>=10000){ $pdo->commit(); $pdo->beginTransaction(); $batch=0; fwrite(STDOUT,"."); }
}
$pdo->commit(); fclose($in);

$cnt=(int)$pdo->query('SELECT COUNT(*) FROM services')->fetchColumn();
$sample=$pdo->query('SELECT * FROM services ORDER BY claim_service_id ASC LIMIT 5')->fetchAll();

fwrite(STDOUT, "\nDone. Processed $total; affected ~$affected; services total: $cnt; skipped (missing claim_id): $skipped" . ($skipped?" e.g. ".implode(',', $skippedExamples):'') . "\n");
foreach($sample as $r){ fwrite(STDOUT, sprintf("%s,%s,%s,%s,%s\n", $r['claim_service_id'],$r['claim_id'],$r['service_id'],$r['service_name'],$r['qty_provided'])); }

