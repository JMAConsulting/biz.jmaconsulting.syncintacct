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

  public static function fetchTransactionrecords($batchID, $entityType) {
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
      fi.description AS item_description
      FROM civicrm_entity_batch eb
      LEFT JOIN civicrm_financial_trxn ft ON (eb.entity_id = ft.id AND eb.entity_table = 'civicrm_financial_trxn')
      LEFT JOIN civicrm_financial_account fa_to ON fa_to.id = ft.to_financial_account_id
      LEFT JOIN civicrm_financial_account fa_from ON fa_from.id = ft.from_financial_account_id
      LEFT JOIN civicrm_option_group cog ON cog.name = 'payment_instrument'
      LEFT JOIN civicrm_option_value cov ON (cov.value = ft.payment_instrument_id AND cov.option_group_id = cog.id)
      LEFT JOIN civicrm_entity_financial_trxn eftc ON (eftc.financial_trxn_id  = ft.id AND eftc.entity_table = '{$entityType}')
      LEFT JOIN civicrm_contribution c ON c.id = eftc.entity_id
      LEFT JOIN civicrm_option_group cog_status ON cog_status.name = 'contribution_status'
      LEFT JOIN civicrm_option_value cov_status ON (cov_status.value = ft.status_id AND cov_status.option_group_id = cog_status.id)
      LEFT JOIN civicrm_entity_financial_trxn efti ON (efti.financial_trxn_id  = ft.id AND efti.entity_table = 'civicrm_financial_item')
      LEFT JOIN civicrm_financial_item fi ON fi.id = efti.entity_id
      LEFT JOIN civicrm_financial_account fac ON fac.id = fi.financial_account_id
      LEFT JOIN civicrm_financial_account fa ON fa.id = fi.financial_account_id
      WHERE eb.batch_id = ( %1 )";


    $params = array(1 => array(, 'Integer'));
    $dao = CRM_Core_DAO::executeQuery($sql, $params);
  }


}
