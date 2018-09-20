
CREATE TABLE `civicrm_intacct_batches` (
   `id` INT NOT NULL AUTO_INCREMENT ,
   `batch_id` INT NOT NULL ,
   `mode` CHAR(2) NOT NULL ,
  PRIMARY KEY (`id`),
  CONSTRAINT UI_batch_id_mode UNIQUE (`batch_id`, `mode`)
) ENGINE = InnoDB;

CREATE TABLE `civicrm_intacct_financial_account_data` (
  `financial_account_id` int(10) UNSIGNED NOT NULL COMMENT 'Financial Account ID',
  `class_id` varchar(20) DEFAULT NULL COMMENT 'Intacct Class ID',
  `dept_id` varchar(20) DEFAULT NULL COMMENT 'Intacct Department ID',
  `location` varchar(50) DEFAULT NULL COMMENT 'Intacct Location',
  `project_id` varchar(20) DEFAULT NULL COMMENT 'Intacct Project ID',
  PRIMARY KEY (`financial_account_id`),
  CONSTRAINT UI_intacct_fa_unique UNIQUE (`class_id`,`dept_id`,`location`,`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
