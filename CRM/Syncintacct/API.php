<?php

require __DIR__ . '/../../vendor/autoload.php';

use Intacct\ClientConfig;
use Intacct\OnlineClient;
use Intacct\Functions\Common\ReadByQuery;

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
  protected $_credential;

  /**
   * The constructor sets search parameters and instantiate CRM_Utils_HttpClient
   */
  public function __construct() {
    $this->_credential = Civi::settings()->get('intacct_credential');
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

  /**
   * Function to call core_user_get_users webservice to fetch moodle user
   */
  public function getVendors() {
    return $this->sendRequest('VENDOR');
  }

  /**
   * Function used to make Moodle API request
   *
   * @param string $apiFunc
   *   Donor Search API function name
   *
   * @return array
   */
  public function sendRequest($entity) {

    $clientConfig = new ClientConfig();

    $clientConfig->setCompanyId('AGBU-DEV');
    $clientConfig->setUserId('xml_gateway');
    $clientConfig->setUserPassword('MASD!H16b4d');
    $clientConfig->setSenderId('AGBU');
    $clientConfig->setSenderPassword('Armenia!2018');


    //$clientConfig->setProfileFile(__DIR__ . '/.credentials.ini');

    $client = new OnlineClient($clientConfig);

    $query = new ReadByQuery();
    $query->setObjectName('VENDOR');
    $query->setPageSize(1); // Keep the count to just 1 for the example
    $query->setFields([
       'RECORDNO',
       'VENDORID',
       'NAME',
    ]);

    $response = $client->execute($query);
    return $response->getResult();
    /*
    $clientConfig = new ClientConfig();
    CRM_Core_Error::Debug_var('s', $this->_credential);

    $clientConfig->setCompanyId($this->_credential['company_id']);
    $clientConfig->setUserId($this->_credential['user_id']);
    $clientConfig->setUserPassword($this->_credential['user_password']);
    $clientConfig->setSenderId($this->_credential['sender_id']);
    $clientConfig->setSenderPassword($this->_credential['sender_password']);

    $client = new OnlineClient($clientConfig);

    $query = new ReadByQuery();
    $query->setObjectName($entity);
    $query->setPageSize(1); // Keep the count to just 1 for the example
    $query->setFields([
       'RECORDNO',
       'VENDORID',
       'NAME',
    ]);

    $response = $client->execute($query);
    $result = $response->getResult();

    return $result;

    /**

    return array(
      self::recordError($response),
      $response,
    );*/
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
