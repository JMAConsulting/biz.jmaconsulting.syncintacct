<?php

require_once 'syncintacct.civix.php';

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function syncintacct_civicrm_config(&$config) {
  _syncintacct_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function syncintacct_civicrm_xmlMenu(&$files) {
  _syncintacct_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function syncintacct_civicrm_install() {
  _syncintacct_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function syncintacct_civicrm_uninstall() {
  _syncintacct_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function syncintacct_civicrm_enable() {
  _syncintacct_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function syncintacct_civicrm_disable() {
  _syncintacct_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function syncintacct_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _syncintacct_civix_civicrm_upgrade($op, $queue);
}

function syncintacct_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Financial_Form_Export') {
    $optionTypes = array(
      'IIF' => ts('Export to IIF'),
      'CSV' => ts('Export to CSV'),
      'SyncIntacctAP' => ts('Sync to Sage Intacct A/P'),
      'SyncIntacctGL' => ts('Sync to Sage Intacct G/L'),
    );
    $form->addRadio('export_format', NULL, $optionTypes, NULL, '<br/>', TRUE);
    if (!empty($_GET['export_format'])) {
      CRM_Core_Resources::singleton()->addScript(
        "CRM.$(function($) {
          $('input[name=\"export_format\"]').filter('[value=" . $_GET['export_format'] . "]').prop('checked', true);
        });"
      );
    }
  }
}

function syncintacct_civicrm_validateForm($formName, &$fields, &$files, &$form, &$errors) {
  if ($formName == 'CRM_Financial_Form_Export') {
    $exporters = [
      'SyncIntacctAP' => ts('Sync to Sage Intacct A/P'),
      'SyncIntacctGL' => ts('Sync to Sage Intacct G/L'),
    ];
    if (in_array($fields['export_format'], array_keys($exporters))) {
      $batchIds = (array) $form->getVar('_batchIds');
      $grantBatches = CRM_Syncintacct_Util::batchesByEntityTable($batchIds, 'civicrm_grant');
      $contributionBatches = CRM_Syncintacct_Util::batchesByEntityTable($batchIds, 'civicrm_contribution');
      if ($grantBatches == 0) {
        if ($fields['export_format'] == 'SyncIntacctAP') {
          $errors['export_format'] = ts('Selected batches has no grant payments. Please go back and make sure that selected batches has transactions related to grant payment only, to proceed with "%1" option', [1 => $exporters['SyncIntacctAP']]);
        }
      }
      elseif ($contributionBatches == 0) {
        if ($fields['export_format'] == 'SyncIntacctGL') {
          $errors['export_format'] = ts('Selected batches has no contribution payments. Please go back and make sure that selected batches has transactions related to contributions only, to proceed with "%1" option', [1 => $exporters['SyncIntacctGL']]);
        }
      }
      elseif ($grantBatches != 0 && $contributionBatches != 0) {
        $errors['export_format'] = ts('Selected batch(es) should only contain transactions related to %1 ', [
          1 => (($fields['export_format'] == 'SyncIntacctAP') ? 'Grant payment' : 'Contribution'),
        ]);
      }
    }
  }
}


/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function syncintacct_civicrm_managed(&$entities) {
  _syncintacct_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function syncintacct_civicrm_caseTypes(&$caseTypes) {
  _syncintacct_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function syncintacct_civicrm_angularModules(&$angularModules) {
_syncintacct_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function syncintacct_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _syncintacct_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function syncintacct_civicrm_preProcess($formName, &$form) {

}

*/
