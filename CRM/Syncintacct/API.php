<?php

require __DIR__ . '/../../vendor/autoload.php';

use Intacct\ClientConfig;
use Intacct\OnlineClient;
use Intacct\Functions\Common\ReadByQuery;
use Intacct\Functions\AccountsPayable\VendorCreate;
use Intacct\Functions\Common\Query\QueryString;
use Intacct\Functions\Traits\CustomFieldsTrait;
use Intacct\Functions\GeneralLedger\JournalEntryCreate;
use Intacct\Functions\GeneralLedger\JournalEntryLineCreate;

/**
 * Class to send Moodle API request
 */
class CRM_Syncintacct_API {

  /**
   * Instance of this object.
   *
   * @var CRM_Syncintacct_API
   */
  public static $_singleton = NULL;

  /**
   * Variable to store Moodle web domain
   *
   * @var string
   */
  protected $_client;

  /**
   * The constructor sets search parameters and instantiate CRM_Utils_HttpClient
   */
  public function __construct() {
    $credential = Civi::settings()->get('intacct_credential');

    $clientConfig = new ClientConfig();
    $clientConfig->setCompanyId($credential['company_id']);
    $clientConfig->setUserId($credential['user_id']);
    $clientConfig->setUserPassword($credential['user_password']);
    $clientConfig->setSenderId($credential['sender_id']);
    $clientConfig->setSenderPassword($credential['sender_password']);

    $this->_client = new OnlineClient($clientConfig);
  }

  /**
   * Singleton function used to manage this object.
   *
   * @param array $searchParams
   *   Moodle parameters
   *
   * @return CRM_Syncintacct_API
   */
  public static function &singleton($reset = FALSE) {
    if (self::$_singleton === NULL || $reset) {
      self::$_singleton = new CRM_Syncintacct_API();
    }
    return self::$_singleton;
  }

  public function createVendors($displayName) {
    $vendorCreate = new VendorCreate();
    $vendorCreate->setVendorName($displayName);
    return $this->sendRequest($vendorCreate);
  }

  /**
   * Function to fetch vendors
   */
  public function getVendors($displayNames, $searchParams = ['RECORDNO', 'VENDORID', 'NAME']) {
    $queryString = new QueryString(sprintf("NAME IN ('%s')", implode("', '", $displayNames)));
    $query = new ReadByQuery();
    $query->setObjectName('VENDOR');
    $query->setQuery($queryString);
    $query->setFields($searchParams);

    return $this->sendRequest($query);
  }

  public function createGLEntry($entry) {
    $journalLineEntry = new JournalEntryLineCreate();
    $journalLineEntry->setGlAccountNumber($entry['ACCOUNTNO']);
    $journalLineEntry->setVendorId($entry['VENDORID']);
    $journalLineEntry->setTransactionCurrency($entry['CURRENCY']);
    $journalLineEntry->setTransactionAmount($entry['AMOUNT']);
    $journalLineEntry->setMemo($entry['description']);
    $journalLineEntry->setCustomAllocationSplits(CustomFieldsTrait($entry['customfields']));
    return $journalEntry;
  }

  public function createGLBatch($GLBatch) {
    $journalEntry = new JournalEntryCreate();
    $journalEntry->setJournalSymbol($GLBatch['JOURNAL']);
    $journalEntry->setPostingDate($GLBatch['BATCH_DATE']);
    $journalEntry->setDescription($GLBatch['BATCH_TITLE']);
    $journalEntry->setLines($GLBatch['ENTRIES']);

    return $this->sendRequest($journalEntry);
  }

  /**
   * Function used to make Intacct API request
   *
   * @param string $entity
   * @param string $searchParams
   *
   * @return array
   */
  public function sendRequest($query) {
    return $this->_client->execute($query)->getResult();
  }

  /**
   * Record error response if there's anything wrong in $response
   *
   * @param string $response
   *   fetched data from Moodle API
   *
   * @return bool
   *   Found error ? TRUE or FALSE
   */
  public static function recordError($response) {
    $isError = FALSE;
    $response = json_decode($response, TRUE);

    if (!empty($response['exception'])) {
      civicrm_api3('SystemLog', 'create', array(
        'level' => 'error',
        'message' => $response['message'],
        'contact_id' => CRM_Core_Session::getLoggedInContactID(),
      ));
      $isError = TRUE;
    }

    return $isError;
  }

}
