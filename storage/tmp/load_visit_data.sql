SET SESSION sql_mode='';
SET SESSION foreign_key_checks = 0;

-- Load visit_data.csv into visit_data table
LOAD DATA LOCAL INFILE '/Users/shauritanga/Desktop/Web/pharmaccess/public/data/visit_data.csv'
REPLACE INTO TABLE visit_data
CHARACTER SET utf8mb4
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' ESCAPED BY '\\'
LINES TERMINATED BY '\r\n'
IGNORE 1 LINES
(@ClaimID, @InsureeID, @FamilyID, @MatibabuID, @PPIScore, @Gender, @DateFrom, @DateTo, @ICDID, @HFID, @HFName, @ClaimAdminId, @ICDID1, @ICDID2, @ICDID3, @ICDID4, @VisitType, @VisitType2, @VisitType3, @DOB, @DistrictName, @Shehia)
SET
  claim_id        = IF(UPPER(TRIM(REPLACE(@ClaimID, 'ï»¿', ''))) IN ('', 'NULL'), NULL, TRIM(REPLACE(@ClaimID, 'ï»¿', ''))),
  insuree_id      = IF(UPPER(TRIM(@InsureeID)) IN ('', 'NULL'), NULL, TRIM(@InsureeID)),
  family_id       = IF(UPPER(TRIM(@FamilyID)) IN ('', 'NULL'), NULL, TRIM(@FamilyID)),
  matibabu_id     = IF(UPPER(TRIM(@MatibabuID)) IN ('', 'NULL'), NULL, TRIM(@MatibabuID)),
  ppi_score       = IF(UPPER(TRIM(@PPIScore)) IN ('', 'NULL'), NULL, TRIM(@PPIScore)),
  gender          = IF(UPPER(TRIM(@Gender)) IN ('', 'NULL'), NULL, TRIM(@Gender)),
  date_from       = IF(UPPER(TRIM(@DateFrom)) IN ('', 'NULL'), NULL, STR_TO_DATE(SUBSTRING_INDEX(TRIM(@DateFrom),' ',1), '%m/%d/%Y')),
  date_to         = IF(UPPER(TRIM(@DateTo))   IN ('', 'NULL'), NULL, STR_TO_DATE(SUBSTRING_INDEX(TRIM(@DateTo),' ',1), '%m/%d/%Y')),
  icd_id          = IF(UPPER(TRIM(@ICDID))    IN ('', 'NULL'), NULL, TRIM(@ICDID)),
  hf_id           = IF(UPPER(TRIM(@HFID))     IN ('', 'NULL'), NULL, TRIM(@HFID)),
  hf_name         = IF(UPPER(TRIM(@HFName))   IN ('', 'NULL'), NULL, TRIM(@HFName)),
  claim_admin_id  = IF(UPPER(TRIM(@ClaimAdminId)) IN ('', 'NULL'), NULL, TRIM(@ClaimAdminId)),
  icd_id_1        = IF(UPPER(TRIM(@ICDID1))   IN ('', 'NULL'), NULL, TRIM(@ICDID1)),
  icd_id_2        = IF(UPPER(TRIM(@ICDID2))   IN ('', 'NULL'), NULL, TRIM(@ICDID2)),
  icd_id_3        = IF(UPPER(TRIM(@ICDID3))   IN ('', 'NULL'), NULL, TRIM(@ICDID3)),
  icd_id_4        = IF(UPPER(TRIM(@ICDID4))   IN ('', 'NULL'), NULL, TRIM(@ICDID4)),
  visit_type      = IF(UPPER(TRIM(@VisitType))  IN ('', 'NULL'), NULL, TRIM(@VisitType)),
  visit_type_2    = IF(UPPER(TRIM(@VisitType2)) IN ('', 'NULL'), NULL, TRIM(@VisitType2)),
  visit_type_3    = IF(UPPER(TRIM(@VisitType3)) IN ('', 'NULL'), NULL, TRIM(@VisitType3)),
  dob             = IF(UPPER(TRIM(@DOB)) IN ('', 'NULL'), NULL, STR_TO_DATE(SUBSTRING_INDEX(TRIM(@DOB),' ',1), '%m/%d/%Y')),
  district_name   = IF(UPPER(TRIM(@DistrictName)) IN ('', 'NULL'), NULL, TRIM(@DistrictName)),
  shehia          = IF(UPPER(TRIM(@Shehia)) IN ('', 'NULL'), NULL, TRIM(@Shehia));

SET SESSION foreign_key_checks = 1;

