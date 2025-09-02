<?php
// Usage: php scripts/import_risk_profile.php public/data/risk_profile.csv
// Imports risk_profile.csv into risk_profile table with upsert and FK checks

if ($argc < 2) { fwrite(STDERR, "Usage: php {$argv[0]} <risk_profile.csv>\n"); exit(1);}
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

$idxId=$map['riskprofileid']??$map['id']??null;
$idxInsuree=$map['insureeid']??null;
$idxClaim=$map['claimid']??null;
$idxRefClinic=$map['refclinic']??null;
$idxSupportHelp=$map['supporthelp']??null;
$idxTimeReach=$map['timereachclinic']??null;
$idxVisitsDuring=$map['visitsduringpreg']??null;
$idxHealthCond=$map['healthconditions']??null;
$idxPregnantBefore=$map['pregnantbefore']??null;
$idxAgeFirstChild=$map['agefirstchild']??null;
$idxChildrenBefore=$map['childrenbefore']??null;
$idxComplications=$map['complicationpreviouspregnancies']??null;
$idxAttend=$map['attendhealthfacility']??null;

if(in_array(null, [$idxId,$idxInsuree,$idxClaim,$idxRefClinic,$idxSupportHelp,$idxTimeReach,$idxVisitsDuring,$idxHealthCond,$idxPregnantBefore,$idxAgeFirstChild,$idxChildrenBefore,$idxComplications,$idxAttend], true)){
  fwrite(STDERR, "Missing required headers. Found: ".implode(',', $header)."\n"); exit(5);
}

$nullIf=function($s){ $s=trim((string)$s); if($s===''||strcasecmp($s,'null')===0||$s==='" "'||$s==='""') return null; return $s; };
$toInt=function($s) use($nullIf){ $v=$nullIf($s); return $v===null?null:(int)$v; };
$toStr=function($s) use($nullIf){ $v=$nullIf($s); return $v===null?null:$v; };

$allowedPowers = array_flip([1,2,4,8,16,32,64,128]);
$normalizeFkBit = function($v) use ($allowedPowers) {
    if ($v === null) return null;
    if ($v === 0) return null; // store NULL instead of 0 to satisfy FK
    return isset($allowedPowers[$v]) ? $v : null; // only accept single-bit values
};

$existsClaim=$pdo->prepare('SELECT 1 FROM visit_data WHERE claim_id=? LIMIT 1');

$sql="INSERT INTO risk_profile (
  risk_profile_id, insuree_id, claim_id, ref_clinic, support_help, time_reach_clinic, visits_during_preg,
  health_conditions, pregnant_before, age_first_child, children_before, complication_previous_pregnancies,
  attend_health_facility
) VALUES (
  :risk_profile_id, :insuree_id, :claim_id, :ref_clinic, :support_help, :time_reach_clinic, :visits_during_preg,
  :health_conditions, :pregnant_before, :age_first_child, :children_before, :complication_previous_pregnancies,
  :attend_health_facility
) ON DUPLICATE KEY UPDATE
  insuree_id=VALUES(insuree_id), claim_id=VALUES(claim_id), ref_clinic=VALUES(ref_clinic), support_help=VALUES(support_help),
  time_reach_clinic=VALUES(time_reach_clinic), visits_during_preg=VALUES(visits_during_preg), health_conditions=VALUES(health_conditions),
  pregnant_before=VALUES(pregnant_before), age_first_child=VALUES(age_first_child), children_before=VALUES(children_before),
  complication_previous_pregnancies=VALUES(complication_previous_pregnancies), attend_health_facility=VALUES(attend_health_facility)";
$stmt=$pdo->prepare($sql);

$pdo->beginTransaction();
$total=0; $affected=0; $skipped=0; $skippedExamples=[]; $batch=0;
while(($row=fgetcsv($in,0,',','"','\\'))!==false){
  $total++;
  $riskId=$toInt($row[$idxId]??null);
  $insuree=$toInt($row[$idxInsuree]??null);
  $claim=$toInt($row[$idxClaim]??null);
  $ref=$toStr($row[$idxRefClinic]??null);
  $support=$toStr($row[$idxSupportHelp]??null);
  $timeReach=$toInt($row[$idxTimeReach]??null);
  $visits=$toInt($row[$idxVisitsDuring]??null);
  $health=$normalizeFkBit($toInt($row[$idxHealthCond]??null));
  $pregBefore=$toInt($row[$idxPregnantBefore]??null);
  $ageFirst=$toInt($row[$idxAgeFirstChild]??null);
  $childrenBefore=$toInt($row[$idxChildrenBefore]??null);
  $compPrev=$normalizeFkBit($toInt($row[$idxComplications]??null));
  $attend=$toInt($row[$idxAttend]??null);

  if($riskId===null){ continue; }
  // Required fields: claim_id and insuree_id
  if($claim===null){ $skipped++; if(count($skippedExamples)<5){$skippedExamples[]='null-claim';} continue; }
  if($insuree===null){ $skipped++; if(count($skippedExamples)<5){$skippedExamples[]='null-insuree';} continue; }
  $existsClaim->execute([$claim]);
  if($existsClaim->fetchColumn()===false){ $skipped++; if(count($skippedExamples)<5){$skippedExamples[]=(string)$claim;} continue; }

  $stmt->execute([
    ':risk_profile_id'=>$riskId,
    ':insuree_id'=>$insuree,
    ':claim_id'=>$claim,
    ':ref_clinic'=>$ref,
    ':support_help'=>$support,
    ':time_reach_clinic'=>$timeReach,
    ':visits_during_preg'=>$visits,
    ':health_conditions'=>$health,
    ':pregnant_before'=>$pregBefore,
    ':age_first_child'=>$ageFirst,
    ':children_before'=>$childrenBefore,
    ':complication_previous_pregnancies'=>$compPrev,
    ':attend_health_facility'=>$attend,
  ]);
  $affected += $stmt->rowCount();

  if(++$batch>=5000){ $pdo->commit(); $pdo->beginTransaction(); $batch=0; fwrite(STDOUT, "."); }
}
$pdo->commit(); fclose($in);

$cnt=(int)$pdo->query('SELECT COUNT(*) FROM risk_profile')->fetchColumn();

fwrite(STDOUT, "\nDone. Processed $total; affected ~$affected; risk_profile total: $cnt; skipped: $skipped" . ($skipped?" e.g. ".implode(',', $skippedExamples):'') . "\n");
$sample=$pdo->query('SELECT risk_profile_id, insuree_id, claim_id, attend_health_facility FROM risk_profile ORDER BY risk_profile_id ASC LIMIT 5')->fetchAll();
foreach($sample as $r){ fwrite(STDOUT, sprintf("%s,%s,%s,%s\n", $r['risk_profile_id'],$r['insuree_id'],$r['claim_id'],$r['attend_health_facility'])); }

