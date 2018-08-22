<?php

function civicrm_api3_intacct_processBatchSyncToIntacct($params) {
  $dao = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_intacct_batches ORDER BY id ASC');
  while($dao->fetch()) {
    $entityTable = ($dao->mode == 'GL') ? 'civicrm_contribution' : 'civicrm_grant';
    $batchEntries = CRM_Syncintacct_Util::fetchTransactionrecords($dao->batch_id, $entityTable);
    CRM_Syncintacct_Util::createGLEntries($batchEntries);
  }

}
