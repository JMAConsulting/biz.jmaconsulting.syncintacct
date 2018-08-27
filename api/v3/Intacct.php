<?php

/**
 * Intacct.SyncFinancialAccount API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_intacct_SyncFinancialAccount($params) {
  $financialAccounts = civicrm_api3('FinancialAccount', 'get', [
    'return' => ["name", "accounting_code"],
    'options' => ['limit' => 0],
  ])['values'];

  foreach ($financialAccounts as $id => $financialAccount) {
    $found = CRM_Syncintacct_API::singleton()
              ->getGLAccount($financialAccount['accounting_code'])
              ->getTotalCount();
    if ($found == 0) {
      CRM_Syncintacct_API::singleton()->createGLAccount($financialAccount);
    }
  }

  return civicrm_api3_create_success(TRUE, $params, 'Intacct', 'SyncFinancialAccount');
}

/**
 * Job.ProcessBatchSyncToIntacct API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_intacct_processBatchSyncToIntacct($params) {
  $dao = CRM_Core_DAO::executeQuery('SELECT * FROM civicrm_intacct_batches ORDER BY id ASC');
  while($dao->fetch()) {
    $entityTable = ($dao->mode == 'GL') ? 'civicrm_contribution' : 'civicrm_grant';
    $batchEntries = CRM_Syncintacct_Util::fetchTransactionrecords($dao->batch_id, $entityTable);
    $response = CRM_Syncintacct_Util::createGLEntries($batchEntries);
    CRM_Core_Error::Debug_var('res', $response);
    CRM_Syncintacct_Util::processSyncIntacctResponse($dao->batch_id, $response);
  }

  return civicrm_api3_create_success(TRUE, $params, 'Intacct', 'processBatchSyncToIntacct');
}
