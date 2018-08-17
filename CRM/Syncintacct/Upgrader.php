<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Syncintacct_Upgrader extends CRM_Syncintacct_Upgrader_Base {

  // By convention, functions that look like "function upgrade_NNNN()" are
  // upgrade tasks. They are executed in order (like Drupal's hook_update_N).


  public function install() {
    civicrm_api3('Navigation', 'create', array(
      'label' => ts('CiviCRM Sage Intacct Integration', array('domain' => 'biz.jmaconsulting.syncintacct')),
      'name' => 'intacct_setting',
      'url' => 'civicrm/intacct/setting?reset=1',
      'domain_id' => CRM_Core_Config::domainID(),
      'is_active' => 1,
      'parent_id' => civicrm_api3('Navigation', 'getvalue', array(
        'return' => "id",
        'name' => "System Settings",
      )),
      'permission' => 'administer CiviCRM',
    ));
  }


  public function uninstall() {
   self::changeNavigation('delete');
  }

  public function enable() {
    self::changeNavigation('enable');
  }

  public function disable() {
    self::changeNavigation('disable');
  }

  /**
   * disable/enable/delete Intacct Setting link
   *
   * @param string $action
   * @throws \CiviCRM_API3_Exception
   */
  public static function changeNavigation($action) {
    $names = ['intacct_setting'];
    foreach ($names as $name) {
      if ($action == 'delete') {
        $id = civicrm_api3('Navigation', 'getvalue', array(
          'return' => "id",
          'name' => $name,
        ));
        if ($id) {
          civicrm_api3('Navigation', 'delete', array('id' => $id));
        }
      }
      else {
        $isActive = ($action == 'enable') ? 1 : 0;
        CRM_Core_BAO_Navigation::setIsActive(
          CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Navigation', $name, 'id', 'name'),
          $isActive
        );
      }
    }
    CRM_Core_BAO_Navigation::resetNavigation();
  }

}
