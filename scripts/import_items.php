<?php
// Usage: php scripts/import_items.php public/data/items.csv
// Imports items.csv into items table (columns: claim_item_id, claim_id, item_id, item_name, qty_provided)

if ($argc < 2) { fwrite(STDERR, "Usage: php {$argv[0]} <items.csv>\n"); exit(1);} 
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

$idxClaimItem=$map['claimitemid']??$map['id']??null;
$idxClaim=$map['claimid']??null;
$idxItem=$map['itemid']??null;
$idxName=$map['itemname']??null;
$idxQty=$map['qtyprovided']??$map['quantity']??null;
if($idxClaimItem===null||$idxClaim===null||$idxItem===null||$idxName===null||$idxQty===null){
  fwrite(STDERR,"Missing required headers. Found: ".implode(',', $header)."\n"); exit(5);
}

$sql="INSERT INTO items (claim_item_id, claim_id, item_id, item_name, qty_provided) VALUES (:cid,:cl,:it,:nm,:qt) ON DUPLICATE KEY UPDATE claim_id=VALUES(claim_id), item_id=VALUES(item_id), item_name=VALUES(item_name), qty_provided=VALUES(qty_provided)";
$stmt=$pdo->prepare($sql);
$exists=$pdo->prepare('SELECT 1 FROM visit_data WHERE claim_id=? LIMIT 1');

$pdo->beginTransaction();
$total=0; $affected=0; $batch=0; $skipped=0; $skippedExamples=[];
while(($row=fgetcsv($in,0,',','"','\\'))!==false){
  $total++;
  $claimItemId=(int)trim((string)($row[$idxClaimItem]??''));
  $claimId=(int)trim((string)($row[$idxClaim]??''));
  $itemId=(int)trim((string)($row[$idxItem]??''));
  $name=trim((string)($row[$idxName]??''));
  $qty=(int)trim((string)($row[$idxQty]??''));
  if($claimItemId===0){ continue; }
  // Ensure referenced claim exists to satisfy FK
  $exists->execute([$claimId]);
  if($exists->fetchColumn() === false){
    $skipped++;
    if (count($skippedExamples) < 5) { $skippedExamples[] = $claimId; }
    continue;
  }
  $stmt->execute([':cid'=>$claimItemId, ':cl'=>$claimId, ':it'=>$itemId, ':nm'=>$name, ':qt'=>$qty]);
  $affected += $stmt->rowCount();
  if(++$batch>=10000){ $pdo->commit(); $pdo->beginTransaction(); $batch=0; fwrite(STDOUT,"."); }
}
$pdo->commit(); fclose($in);

$cnt=(int)$pdo->query('SELECT COUNT(*) FROM items')->fetchColumn();
$sample=$pdo->query('SELECT * FROM items ORDER BY claim_item_id ASC LIMIT 5')->fetchAll();

fwrite(STDOUT, "\nDone. Processed $total; affected ~$affected; items total: $cnt; skipped (missing claim_id): $skipped" . ($skipped?" e.g. ".implode(',', $skippedExamples):'') . "\n");
foreach($sample as $r){ fwrite(STDOUT, sprintf("%s,%s,%s,%s,%s\n", $r['claim_item_id'],$r['claim_id'],$r['item_id'],$r['item_name'],$r['qty_provided'])); }

