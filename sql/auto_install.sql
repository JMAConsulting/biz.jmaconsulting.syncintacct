
CREATE TABLE `civicrm_intacct_batches` (
   `id` INT NOT NULL AUTO_INCREMENT ,
   `batch_id` INT NOT NULL ,
   `mode` CHAR(2) NOT NULL ,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB;
