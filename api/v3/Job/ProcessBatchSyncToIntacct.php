<?php

/**
 * Job.ProcessBatchSyncToIntacct API specification
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRM/API+Architecture+Standards
 */
function _civicrm_api3_job_processbatchsynctointacct_spec(&$spec) {
}

function civicrm_api3_job_processbatchsynctointacct($params) {
  $dao = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_intacct_batches ORDER BY id ASC');
  while($dao->fetch()) {
    $entityTable = ($dao->mode == 'GL') ? 'civicrm_contribution' : 'civicrm_grant';
    $batchEntries = CRM_Syncintacct_Util::fetchTransactionrecords($dao->batch_id, $entityTable);
    CRM_Syncintacct_Util::createGLEntries($batchEntries);
  }

}
