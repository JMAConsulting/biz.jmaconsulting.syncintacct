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
use Intacct\Functions\AccountsPayable\BillCreate;
use Intacct\Functions\AccountsPayable\BillLineCreate;
use Intacct\Functions\GeneralLedger\CustomAllocationSplit;
use Intacct\Functions\GeneralLedger\AccountCreate;
use Intacct\Exception\ResponseException;

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
    // @TODO this is a dummy active location id passed
    $journalLineEntry->setLocationId('Elim');
    //$journalLineEntry->setLocationId($entry['LOCATION']);
    $this->_setMetaData($journalLineEntry, $entry);
    $journalLineEntry->setMemo($entry['DESCRIPTION']);
    $customFields = new CustomAllocationSplit($entry['customfields']);
    $journalLineEntry->setCustomAllocationSplits($customFields);
    return $journalLineEntry;
  }

  public function createGLBatch($GLBatch) {
    $journalEntry = new JournalEntryCreate();
    $journalEntry->setJournalSymbol('AR');
    $journalEntry->setPostingDate($GLBatch['BATCH_DATE']);
    $journalEntry->setDescription($GLBatch['BATCH_TITLE']);
    $journalEntry->setLines($GLBatch['ENTRIES']);

    return $this->sendRequest($journalEntry);
  }

  public function createAPBatch($APBatch) {
    $billEntry = new BillCreate();
    $billEntry->setVendorId($APBatch['VENDORID']);
    $billEntry->setTransactionDate($APBatch['TRXN_DATE']);
    $billEntry->setDescription($APBatch['DESCRIPTION']);
    $billEntry->setLines($APBatch['ENTRIES']);
    $billEntry->setDueDate($APBatch['DUE_DATE']);
    $billEntry->setTransactionCurrency($APBatch['CURRENCY']);
    $billEntry->getBaseCurrency('USD');

    return $this->sendRequest($billEntry);
  }

  public function createAPEntry($entry) {
    $billLineEntry = new BillLineCreate();
    $billLineEntry->setGlAccountNumber($entry['ACCOUNTNO']);
    $billLineEntry->setTransactionAmount($entry['AMOUNT']);
    //$billLineEntry->setLocationId($entry['LOCATION']);
    $billLineEntry->setLocationId('Elim');
    $this->_setMetaData($billLineEntry, $entry);
    // TODO: BillLineCreate does not support adding custom fields yet
    //  $customFields = new CustomAllocationSplit($entry['customfields']);
    //  $billLineEntry->setCustomAllocationSplits($customFields);
    return $billLineEntry;
  }

  public function _setMetaData(&$entry, $params) {
    $attributes = [
      'DEPARTMENT' => 'setDepartmentId',
      'PROJECTID' => 'setProjectId',
      'CLASSID' => 'setClassId',
    ];
    foreach ($attributes as $attribute => $func) {
      if (!empty($params[$attribute])) {
        $entry->$func($params[$attribute]);
      }
    }
  }

  /**
   * Function to fetch vendors
   */
  public function getGLAccount($FAAccountCode, $searchParams = ['ACCOUNTNO', 'TITLE']) {
    $queryString = new QueryString("ACCOUNTNO = '{$FAAccountCode}'");
    $query = new ReadByQuery();
    $query->setObjectName('GLACCOUNT');
    $query->setQuery($queryString);
    $query->setFields($searchParams);

    return $this->sendRequest($query);
  }

  public function createGLAccount($params) {
    $accountCreate = new AccountCreate();
    $accountCreate->setAccountNo($params['accounting_code']);
    $accountCreate->setTitle($params['name']);

    return $this->sendRequest($accountCreate);
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
    $errorMessage = [];
    try {
      return $this->_client->execute($query)->getResult()->getData();
    } catch (ResponseException $ex) {
      $errorMessage = [
        'is_error' => TRUE,
        get_class($ex) => $ex->getMessage(),
        'Errors: ' => $ex->getErrors(),
      ];
    } catch (\Exception $ex) {
      $errorMessage = [
        'is_error' => TRUE,
        get_class($ex) => $ex->getMessage(),
      ];
    }

    return $errorMessage;

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
