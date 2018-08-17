<?php

require_once 'CRM/Core/Form.php';

class CRM_Syncintacct_Form_Setting extends CRM_Core_Form {
  /**
  * Intact Web service credentials
  *
  * @var string
  */
 protected $_credential;

 /**
  * Set variables up before form is built.
  */
 public function preProcess() {
   if (!CRM_Core_Permission::check('administer CiviCRM')) {
     CRM_Core_Error::fatal(ts('You do not permission to access this page, please contact your system administrator.'));
   }
   $this->_credential = Civi::settings()->get('intacct_credential');
 }
 /**
  * Set default values.
  *
  * @return array
  */
 public function setDefaultValues() {
   return $this->_credential;
 }

 public function buildQuickForm() {
   $this->add('text', 'company_id', ts('Company ID'), array('class' => 'huge'), TRUE);
   $this->add('text', 'user_id', ts('User ID'), array('class' => 'huge'), TRUE);
   $this->add('password', 'user_password', ts('User Password'), array('class' => 'huge'), TRUE);
   $this->add('text', 'sender_id', ts('Sender ID'), array('class' => 'huge'), TRUE);
   $this->add('password', 'sender_password', ts('Sender Password'), array('class' => 'huge'), TRUE);
   $this->assign('intacctCredentials', ['company_id', 'user_id', 'user_password', 'sender_id', 'sender_password']);
   $this->addButtons(array(
     array(
       'type' => 'submit',
       'name' => ts('Submit'),
       'isDefault' => TRUE,
     ),
   ));
   parent::buildQuickForm();
 }

 public function postProcess() {
   $values = $this->exportValues();

   foreach (['company_id', 'user_id', 'user_password', 'sender_id', 'sender_password'] as $attribute) {
     $credential[$attribute] = $values[$attribute];
   }
   Civi::settings()->set('intacct_credential', $credential);

   CRM_Core_Session::setStatus(ts("Intacct Web Service credential submitted"), ts('Success'), 'success');
   CRM_Utils_System::redirect(CRM_Utils_System::url('civicrm', 'reset=1'));
 }

}
