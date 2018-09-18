<?php

/**
 * Class to send Moodle API request
 */
class CRM_Syncintacct_Util {


  /**
   * IF the given array of batch IDs consist of any transactions related to grant payment
   */
  public static function batchesByEntityTable($batchIDs, $entityTable) {
      $sql = "SELECT COUNT(eb.batch_id)
      FROM civicrm_entity_batch eb
      INNER JOIN civicrm_financial_trxn tx ON tx.id = eb.entity_id AND eb.entity_table = 'civicrm_financial_trxn'
      INNER JOIN civicrm_entity_financial_trxn eft ON eft.financial_trxn_id = tx.id AND eft.entity_table = '{$entityTable}'
      INNER JOIN civicrm_batch b ON b.id = eb.batch_id
      WHERE eb.batch_id IN (" . implode(',', $batchIDs) . ")
      GROUP BY eb.batch_id";
      $dao = CRM_Core_DAO::executeQuery($sql);
       return $dao->N;
  }

  public static function createEntries($batchID, $entityType) {
    $entityTable = ($entityType == 'GL') ? 'civicrm_contribution' : 'civicrm_grant';
    $sql = "SELECT
      ft.id as financial_trxn_id,
      ft.trxn_date,
      fa_to.accounting_code AS to_account_code,
      fa_to.name AS to_account_name,
      fa_to.account_type_code AS to_account_type_code,
      ft.total_amount AS debit_total_amount,
      ft.trxn_id AS trxn_id,
      cov.label AS payment_instrument,
      ft.check_number,
      c.source AS source,
      c.id AS contribution_id,
      c.contact_id AS contact_id,
      cc.display_name,
      eb.batch_id AS batch_id,
      ft.currency AS currency,
      cov_status.label AS status,
      CASE
        WHEN efti.entity_id IS NOT NULL
        THEN efti.amount
        ELSE eftc.amount
      END AS amount,
      fa_from.account_type_code AS credit_account_type_code,
      fa_from.accounting_code AS credit_account,
      fa_from.name AS credit_account_name,
      fac.account_type_code AS from_credit_account_type_code,
      fac.accounting_code AS from_credit_account,
      fac.name AS from_credit_account_name,
      fi.description AS item_description,
      fi.id AS financial_item_id,
      eftc.entity_id AS entity_id
      FROM civicrm_entity_batch eb
      LEFT JOIN civicrm_financial_trxn ft ON (eb.entity_id = ft.id AND eb.entity_table = 'civicrm_financial_trxn')
      LEFT JOIN civicrm_financial_account fa_to ON fa_to.id = ft.to_financial_account_id
      LEFT JOIN civicrm_financial_account fa_from ON fa_from.id = ft.from_financial_account_id
      LEFT JOIN civicrm_option_group cog ON cog.name = 'payment_instrument'
      LEFT JOIN civicrm_option_value cov ON (cov.value = ft.payment_instrument_id AND cov.option_group_id = cog.id)
      LEFT JOIN civicrm_entity_financial_trxn eftc ON (eftc.financial_trxn_id  = ft.id AND eftc.entity_table = '{$entityTable}')
      LEFT JOIN civicrm_contribution c ON c.id = eftc.entity_id
      LEFT JOIN civicrm_contact cc ON cc.id = c.contact_id
      LEFT JOIN civicrm_option_group cog_status ON cog_status.name = 'contribution_status'
      LEFT JOIN civicrm_option_value cov_status ON (cov_status.value = ft.status_id AND cov_status.option_group_id = cog_status.id)
      LEFT JOIN civicrm_entity_financial_trxn efti ON (efti.financial_trxn_id  = ft.id AND efti.entity_table = 'civicrm_financial_item')
      LEFT JOIN civicrm_financial_item fi ON fi.id = efti.entity_id
      LEFT JOIN civicrm_financial_account fac ON fac.id = fi.financial_account_id
      LEFT JOIN civicrm_financial_account fa ON fa.id = fi.financial_account_id
      WHERE eb.batch_id = ( %1 )";


    $params = array(1 => array($batchID, 'Integer'));
    $dao = CRM_Core_DAO::executeQuery($sql, $params);

    if ($entityType == 'AP') {
      return self::createAPEntries(self::formatAPBatchParams($dao, $batchID));
    }
    else {
      return self::createGLEntries(self::formatGLBatchParams($dao, $batchID));
    }
  }

  public static function formatGLBatchParams($dao, $batchID) {
    $batch = civicrm_api3('Batch', 'getsingle', ['id' => $batchID]);
    $GLBatch = [
      'JOURNAL' => 'CIVIBATCH' . $batchID,
      'BATCH_DATE' => new DateTime($batch['created_date']),
      'BATCH_TITLE' => $batch['title'],
      'ENTRIES' => [],
    ];
    while ($dao->fetch()) {
      $GLBatch['ENTRIES'][] = [
        'ACCOUNTNO' => $dao->credit_account ?: $dao->from_credit_account,
        'VENDORID' => $dao->display_name,
        'CURRENCY' => $dao->currency,
        'AMOUNT' => -$dao->debit_total_amount,
        'DESCRIPTION' => $dao->item_description,
        'customfields' => [
          'batch_id' => $batchID,
          'financial_trxn_id' => $dao->financial_trxn_id,
          'financial_item_id' => $dao->financial_item_id,
          'url' => CRM_Utils_System::url('civicrm/contact/view/contribution', "reset=1&id={$dao->entity_id}&cid={$dao->contact_id}&action=view"),
        ]
      ];
      $GLBatch['ENTRIES'][] = [
        'ACCOUNTNO' => $dao->to_account_code,
        'VENDORID' => $dao->display_name,
        'CURRENCY' => $dao->currency,
        'AMOUNT' => $dao->debit_total_amount,
        'DESCRIPTION' => $dao->item_description,
        'customfields' => [
          'batch_id' => $batchID,
          'financial_trxn_id' => $dao->financial_trxn_id,
          'financial_item_id' => $dao->financial_item_id,
          'url' => CRM_Utils_System::url('civicrm/contact/view/contribution', "reset=1&id={$dao->entity_id}&cid={$dao->contact_id}&action=view"),
        ],
      ];
    }

    return $GLBatch;
  }

  public static function formatAPBatchParams($dao, $batchID) {
    $APBatch = [];
    while ($dao->fetch()) {
      $APBatch[$dao->entity_id] = [
        'CURRENCY' => $dao->currency,
        'VENDORID' => $dao->display_name,
        'DESCRIPTION' => $dao->item_description,
        'TRXN_DATE' => new DateTime($dao->trxn_date),
        'DUE_DATE' => new DateTime(date('Ymd')),
        'ENTRIES' => [],
      ];
      $APBatch[$dao->entity_id]['ENTRIES'][] = [
        'ACCOUNTNO' => $dao->credit_account ?: $dao->from_credit_account,
        'AMOUNT' => -$dao->debit_total_amount,
        'customfields' => [
          'batch_id' => $batchID,
          'financial_trxn_id' => $dao->financial_trxn_id,
          'financial_item_id' => $dao->financial_item_id,
          'url' => CRM_Utils_System::url('civicrm/contact/view/grant', "reset=1&id={$dao->entity_id}&cid={$dao->contact_id}&action=view"),
        ]
      ];
      $APBatch[$dao->entity_id]['ENTRIES'][] = [
        'ACCOUNTNO' => $dao->to_account_code,
        'AMOUNT' => $dao->debit_total_amount,
        'DESCRIPTION' => $dao->item_description,
        'customfields' => [
          'batch_id' => $batchID,
          'financial_trxn_id' => $dao->financial_trxn_id,
          'financial_item_id' => $dao->financial_item_id,
          'url' => CRM_Utils_System::url('civicrm/contact/view/grant', "reset=1&id={$dao->entity_id}&cid={$dao->contact_id}&action=view"),
        ],
      ];
    }

    return $APBatch;
  }

  public static function createAPEntries($batchEntries) {
    $syncIntacctConfig = CRM_Syncintacct_API::singleton();
    $fetchVendors = $syncIntacctConfig->getVendors(array_unique(CRM_Utils_Array::collect('VENDORID', $batchEntries)));
    $displayNames = [];
    $result = '';
    foreach ($fetchVendors as $vendor) {
      $key = (string) $vendor->NAME;
      $displayNames[$key] = (string) $vendor->VENDORID;
    }

    foreach ($batchEntries as $trxnID => &$entry) {
      $vendorID = CRM_Utils_Array::value($entry['VENDORID'], $displayNames);
      if (strstr($vendorID, 'VEN-')) {
        $entry['VENDORID'] = $vendorID;
      }
      else {
        $result = $syncIntacctConfig->createVendors($entry['VENDORID']);
        if (!empty($result[0])) {
          $entry['VENDORID'] = (string) $result[0]->VENDORID;
        }
      }
      foreach ($entry['ENTRIES'] as $key => $trxn) {
        $entry['ENTRIES'][$key] = $syncIntacctConfig->createAPEntry($trxn);
      }

      $response['Trxn ID -' . $trxnID] = $syncIntacctConfig->createAPBatch($entry);
    }
    CRM_Core_Error::debug_var('a', $response);
    return $response;
  }

  public static function createGLEntries($batchEntries) {
    $syncIntacctConfig = CRM_Syncintacct_API::singleton();
    $fetchVendors = $syncIntacctConfig->getVendors(array_unique(CRM_Utils_Array::collect('VENDORID', $batchEntries['ENTRIES'])));

    $displayNames = [];
    $result = '';
    foreach ($fetchVendors as $vendor) {
      $key = (string) $vendor->NAME;
      $displayNames[$key] = (string) $vendor->VENDORID;
    }

    foreach ($batchEntries['ENTRIES'] as $key => &$entry) {
      $vendorID = CRM_Utils_Array::value($entry['VENDORID'], $displayNames);
      if (strstr($vendorID, 'VEN-')) {
        $entry['VENDORID'] = $vendorID;
      }
      else {
        $result = $syncIntacctConfig->createVendors($entry['VENDORID']);
        if (!empty($result[0])) {
          $batchEntries['ENTRIES'][$key]['VENDORID'] = (string) $result[0]->VENDORID;
        }
      }
      $batchEntries['ENTRIES'][$key] = $syncIntacctConfig->createGLEntry($entry);
    }

    return $syncIntacctConfig->createGLBatch($batchEntries);
  }

  public static function processSyncIntacctResponse($batchID, $response) {
    $activity = civicrm_api3('Activity', 'getsingle', [
      'source_record_id' => $batchID,
      'status_id' => CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'status_id', 'Scheduled'),
      'activity_type_id' => CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'activity_type_id', 'Export Accounting Batch'),
    ]);

    $fileName = CRM_Core_Config::singleton()->uploadDir . 'Financial_Transactions_Response_' . date('YmdHis') . '.txt';
    $content = sprintf('Batch ID - %d: %s', $batchID, var_export($response, TRUE));
    file_put_contents($fileName, $content, FILE_APPEND);

    $activityParams = array(
      'id' => $activity['id'],
      'attachFile_2' => array(
        'uri' => $fileName,
        'type' => 'text/plain',
        'location' => $fileName,
        'upload_date' => date('YmdHis'),
      ),
    );
    if (!empty($response['is_error'])) {
      $email =  Civi::settings()->get('send_error_to_email');
      if ($email) {
        $params = [
          'toEmail' => $email,
          'subject' => ts('Intacct response error for Batch ID ' . $batchID),
          'text' => $content,
          'html' => $content,
        ];
        CRM_Utils_Mail::send($params);
      }
    }
    else {
      $activityParams['status_id'] = CRM_Core_PseudoConstant::getKey('CRM_Activity_BAO_Activity', 'status_id', 'Completed');
      civicrm_api3('Batch', 'create', [
        'id' => $batchID,
        'data' => 'Synchronization completed at ' . date('Y-m-d H:i:s'),
      ]);
      CRM_Core_DAO::executeQuery("DELETE FROM civicrm_intacct_batches WHERE batch_id = " . $batchID);
    }
    CRM_Activity_BAO_Activity::create($activityParams);
  }

}
