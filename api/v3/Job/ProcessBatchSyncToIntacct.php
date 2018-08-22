<?php

/**
 * Job.ProcessBatchSyncToIntacct API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_job_ProcessBatchSyncToIntacct($params) {
  $dao = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_intacct_batches ORDER BY id ASC');
  while($dao->fetch()) {
    $entityTable = ($dao->mode == 'GL') ? 'civicrm_contribution' : 'civicrm_grant';
    $batchEntries = CRM_Syncintacct_Util::fetchTransactionrecords($dao->batch_id, $entityTable);
    CRM_Syncintacct_Util::createGLEntries($batchEntries);
  }
}
