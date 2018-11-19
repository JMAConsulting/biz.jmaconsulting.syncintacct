<?php
return array(
  'intacct_credential' => array(
    'group_name' => 'Intacct Integration',
    'name' => 'intacct_credential',
    'type' => 'Array',
    'add' => '5.6',
    'title' => 'Intacct Credentials',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Intact API Credentials',
    'help_text' => NULL,
  ),
  'send_error_to_email' => array(
    'group_name' => 'Intacct Integration',
    'name' => 'send_error_to_email',
    'type' => 'String',
    'quick_form_type' => 'Element',
    'html_type' => 'text',
    'add' => '5.6',
    'title' => 'Send Error Message To',
    'is_domain' => 1,
    'is_contact' => 0,
    'description' => 'Intacct Error messages will be sent to this address',
    'help_text' => NULL,
  ),
);
