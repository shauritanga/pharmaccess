SET SESSION sql_mode='';
-- Use REPLACE to update existing rows by primary key (icd_id) and insert new ones
LOAD DATA LOCAL INFILE '/Users/shauritanga/Desktop/Web/pharmaccess/public/data/icd_codes.csv'
REPLACE INTO TABLE icd_codes
CHARACTER SET utf8mb4
FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' ESCAPED BY '\\'
LINES TERMINATED BY '\r\n'
IGNORE 1 LINES
(@icd_id, @icd_code, @icd_name)
SET icd_id = NULLIF(TRIM(BOTH '\r' FROM @icd_id), ''),
    icd_code = NULLIF(TRIM(BOTH '\r' FROM @icd_code), ''),
    icd_name = NULLIF(TRIM(BOTH '\r' FROM @icd_name), '');

-- Fallback for LF line endings (run manually if needed)
-- LOAD DATA LOCAL INFILE '/Users/shauritanga/Desktop/Web/pharmaccess/public/data/icd_codes.csv'
-- REPLACE INTO TABLE icd_codes
-- CHARACTER SET utf8mb4
-- FIELDS TERMINATED BY ',' OPTIONALLY ENCLOSED BY '"' ESCAPED BY '\\'
-- LINES TERMINATED BY '\n'
-- IGNORE 1 LINES
-- (@icd_id, @icd_code, @icd_name)
-- SET icd_id = NULLIF(@icd_id, ''), icd_code = NULLIF(@icd_code, ''), icd_name = NULLIF(@icd_name, '');

