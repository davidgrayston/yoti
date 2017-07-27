<?php

use Yoti\ActivityDetails;
use Yoti\YotiClient;

require_once __DIR__ . '/sdk/boot.php';

/**
 * Class YotiConnectHelper.
 *
 * @package Drupal\yoti_connect
 */
class YotiConnectHelper {
  /**
   * Yoti user profile attributes.
   *
   * @var array
   */
  public static $profileFields = array(
    ActivityDetails::ATTR_SELFIE => 'Selfie',
    ActivityDetails::ATTR_PHONE_NUMBER => 'Phone number',
    ActivityDetails::ATTR_DATE_OF_BIRTH => 'Date of birth',
    ActivityDetails::ATTR_GIVEN_NAMES => 'Given names',
    ActivityDetails::ATTR_FAMILY_NAME => 'Family name',
    ActivityDetails::ATTR_NATIONALITY => 'Nationality',
    ActivityDetails::ATTR_GENDER => 'Gender',
    ActivityDetails::ATTR_EMAIL_ADDRESS => 'Email Address',
    ActivityDetails::ATTR_POSTAL_ADDRESS => 'Postal Address',
  );

  /**
   * Running mock requests instead of going to yoti.
   *
   * @return bool
   *   true if it's a mockRequests, false otherwise
   */
  public static function mockRequests() {
    return defined('YOTI_MOCK_REQUEST') && YOTI_MOCK_REQUEST;
  }

  /**
   * Link drupal user to Yoti user.
   *
   * @param mixed $currentUser
   *   Logged in user.
   *
   * @return bool
   *   true if successful, false otherwise.
   */
  public function link($currentUser = NULL) {
    if (!$currentUser) {
      global $user;
      $currentUser = $user;
    }

    $config = self::getConfig();
    // print_r($config);exit;
    $token = (!empty($_GET['token'])) ? $_GET['token'] : NULL;

    // If no token then ignore.
    if (!$token) {
      self::setFlash('Could not get Yoti token.', 'error');

      return FALSE;
    }

    // Init yoti client and attempt to request user details.
    try {
      $yotiClient = new YotiClient($config['yoti_sdk_id'], $config['yoti_pem']['contents']);
      $yotiClient->setMockRequests(self::mockRequests());
      $activityDetails = $yotiClient->getActivityDetails($token);
    }
    catch (Exception $e) {
      self::setFlash('Yoti could not successfully connect to your account.', 'error');

      return FALSE;
    }

    // If unsuccessful then bail.
    if ($yotiClient->getOutcome() != YotiClient::OUTCOME_SUCCESS) {
      self::setFlash('Yoti could not successfully connect to your account.', 'error');

      return FALSE;
    }

    // Check if yoti user exists.
    $drupalYotiUid = $this->getDrupalUid($activityDetails->getUserId());

    // If yoti user exists in db but isn't linked to a drupal account
    // (orphaned row) then delete it.
    if ($drupalYotiUid && $currentUser && $currentUser->uid != $drupalYotiUid && !user_load($drupalYotiUid)) {
      // Remove users account.
      $this->deleteYotiUser($drupalYotiUid);
    }

    // If user isn't logged in.
    if (!$currentUser->uid) {
      // Register new user.
      if (!$drupalYotiUid) {
        $errMsg = NULL;

        // Attempt to connect by email.
        if (!empty($config['yoti_connect_email'])) {
          if (($email = $activityDetails->getEmailAddress())) {
            $byMail = user_load_by_mail($email);
            if ($byMail) {
              $drupalYotiUid = $byMail->uid;
              $this->createYotiUser($drupalYotiUid, $activityDetails);
            }
          }
        }

        // If config only existing enabled then check if user exists, if not
        // then redirect to login page.
        if (!$drupalYotiUid) {
          if (empty($config['yoti_only_existing'])) {
            try {
              $drupalYotiUid = $this->createUser($activityDetails);
            }
            catch (Exception $e) {
              $errMsg = $e->getMessage();
            }
          }
          else {
            self::storeYotiUser($activityDetails);
            drupal_goto('/yoti-connect/register');
          }
        }

        // No user id? no account.
        if (!$drupalYotiUid) {
          // If couldn't create user then bail.
          self::setFlash("Could not create user account. $errMsg", 'error');

          return FALSE;
        }
      }

      // Log user in.
      $this->loginUser($drupalYotiUid);
    }
    else {
      // If current logged in user doesn't match yoti user registered then bail.
      if ($drupalYotiUid && $currentUser->uid != $drupalYotiUid) {
        self::setFlash('This Yoti account is already linked to another account.', 'error');
      }
      // If Drupal user not found in yoti table then create new yoti user.
      elseif (!$drupalYotiUid) {
        $this->createYotiUser($currentUser->uid, $activityDetails);
        self::setFlash('Your Yoti account has been successfully linked.');
      }
    }

    return TRUE;
  }

  /**
   * Unlink account from currently logged in.
   */
  public function unlink() {
    global $user;

    // Unlink.
    if ($user) {
      $this->deleteYotiUser($user->uid);
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Store Yoti user details in the session as a serialised object.
   *
   * @param \Yoti\ActivityDetails $activityDetails
   *   Yoti user details Object.
   */
  public static function storeYotiUser(ActivityDetails $activityDetails) {
    drupal_session_start();
    $_SESSION['yoti-user'] = serialize($activityDetails);
  }

  /**
   * Retrieve Yoti user details from the session.
   *
   * @return \Yoti\ActivityDetails|null
   *   Returns Yoti user details from the session or Null.
   */
  public static function getYotiUserFromStore() {
    drupal_session_start();
    return array_key_exists('yoti-user', $_SESSION) ? unserialize($_SESSION['yoti-user']) : NULL;
  }

  /**
   * Remove Yoti user details from the session.
   */
  public static function clearYotiUserStore() {
    drupal_session_start();
    unset($_SESSION['yoti-user']);
  }

  /**
   * Set notification message.
   *
   * @param string $message
   *   Notification message to be displayed.
   * @param string $type
   *   Type of notification, example status.
   */
  public static function setFlash($message, $type = 'status') {
    drupal_set_message($message, $type);
  }

  /**
   * Generate new Yoti username or nickname.
   *
   * @param string $prefix
   *   Yoti user nickname prefix.
   *
   * @return string
   *   Full generated Yoti generated user nickname.
   */
  private function generateUsername($prefix = 'yoticonnect-') {
    // Generate username.
    $i = 0;
    do {
      $username = $prefix . $i++;
    } while (user_load_by_name($username));

    return $username;
  }

  /**
   * Generate Yoti user email.
   *
   * @param string $prefix
   *   Yoti user email prefix.
   * @param string $domain
   *   Email domain.
   *
   * @return string
   *   Full generated Yoti user email
   */
  private function generateEmail($prefix = 'yoticonnect-', $domain = 'example.com') {
    // Generate email.
    $i = 0;
    do {
      $email = $prefix . $i++ . "@$domain";
    } while (user_load_by_mail($email));

    return $email;
  }

  /**
   * Generate user password.
   *
   * @param int $length
   *   Number of characters.
   *
   * @return string
   *   Full generated password
   */
  private function generatePassword($length = 10) {
    // Generate password.
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    // Remember to declare $pass as an array.
    $password = '';
    // Put the length -1 in cache.
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $length; $i++) {
      $n = rand(0, $alphaLength);
      $password .= $alphabet[$n];
    }

    return $password;
  }

  /**
   * Create Yoti user account.
   *
   * @param \Yoti\ActivityDetails $activityDetails
   *   Yoti user details.
   *
   * @return int
   *   User ID
   *
   * @throws Exception
   */
  private function createUser(ActivityDetails $activityDetails) {
    $user = array(
      'status' => 1,
    );

    // Mandatory settings.
    $user['pass'] = $this->generatePassword();
    $user['mail'] = $user['init'] = $this->generateEmail();
    // This username must be unique and accept only a-Z,0-9, - _ @ .
    $user['name'] = $this->generateUsername();

    // The first parameter is sent blank so a new user is created.
    $user = user_save('', $user);

    // Set new id.
    $userId = $user->uid;
    $this->createYotiUser($userId, $activityDetails);

    return $userId;
  }

  /**
   * Returns drupal user unique ID.
   *
   * @param int $yotiId
   *   Yoti user ID.
   * @param string $field
   *   Yoti user identifier.
   *
   * @return int
   *   User unique ID
   */
  private function getDrupalUid($yotiId, $field = "identifier") {
    $tableName = self::tableName();
    $col = db_query("SELECT uid FROM `{$tableName}` WHERE `{$field}` = '$yotiId'")->fetchCol();
    return ($col) ? reset($col) : NULL;
  }

  /**
   * Create user account with Yoti user details.
   *
   * @param int $userId
   *   Created user ID.
   * @param \Yoti\ActivityDetails $activityDetails
   *   Yoti user details.
   */
  public function createYotiUser($userId, ActivityDetails $activityDetails) {
    // $user = user_load($userId);
    $meta = $activityDetails->getProfileAttribute();
    // don't save selfie to db.
    unset($meta[ActivityDetails::ATTR_SELFIE]);

    $selfieFilename = NULL;
    if (($content = $activityDetails->getSelfie())) {
      $uploadDir = self::uploadDir();
      if (!is_dir($uploadDir)) {
        drupal_mkdir($uploadDir, 0777, TRUE);
      }

      $selfieFilename = md5("selfie" . time()) . ".png";
      file_put_contents("$uploadDir/$selfieFilename", $content);
      $meta['selfie_filename'] = $selfieFilename;
    }

    db_insert(self::tableName())->fields(array(
      'uid' => $userId,
      'identifier' => $activityDetails->getUserId(),
      'phone_number' => $activityDetails->getPhoneNumber(),
      'date_of_birth' => $activityDetails->getDateOfBirth(),
      'given_names' => $activityDetails->getGivenNames(),
      'family_name' => $activityDetails->getFamilyName(),
      'nationality' => $activityDetails->getNationality(),
      'gender' => $activityDetails->getGender(),
      'email_address' => $activityDetails->getEmailAddress(),
      'selfie_filename' => $selfieFilename,
      'data' => serialize($meta),
    ))->execute();
  }

  /**
   * Delete Yoti user from Drupal.
   *
   * @param int $userId
   *   Drupal user id.
   */
  private function deleteYotiUser($userId) {
    db_delete(self::tableName())->condition("uid", $userId)->execute();
  }

  /**
   * Submit user log in request.
   *
   * @param int $userId
   *   Drupal user ID.
   */
  private function loginUser($userId) {
    $form_state['uid'] = $userId;
    user_login_submit(array(), $form_state);
  }

  /**
   * Not used in this instance.
   *
   * @return string
   *   Yoti user database table name.
   */
  public static function tableName() {
    return 'users_yoti';
  }

  /**
   * Returns Yoti upload directory path.
   *
   * @param bool $realPath
   *   If true returns directory real path, false otherwise.
   *
   * @return string
   *   Yoti upload directory path
   */
  public static function uploadDir($realPath = TRUE) {
    return ($realPath) ? drupal_realpath("yoti://") : 'yoti://';
  }

  /**
   * Returns Yoti upload directory URL.
   *
   * @return string
   *   Yoti upload directory URL
   */
  public static function uploadUrl() {
    return file_create_url(self::uploadDir());
  }

  /**
   * Returns Yoti config data.
   *
   * @return array
   *   Yoti config data
   */
  public static function getConfig() {
    $pem = variable_get('yoti_pem');
    $name = $contents = NULL;
    if ($pem) {
      $file = file_load($pem);
      $name = $file->uri;
      $contents = file_get_contents(drupal_realpath($name));
    }
    $config = array(
      'yoti_app_id' => variable_get('yoti_app_id'),
      'yoti_scenario_id' => variable_get('yoti_scenario_id'),
      'yoti_sdk_id' => variable_get('yoti_sdk_id'),
      'yoti_only_existing' => variable_get('yoti_only_existing'),
      'yoti_success_url' => variable_get('yoti_success_url', '/user'),
      'yoti_fail_url' => variable_get('yoti_fail_url', '/'),
      'yoti_connect_email' => variable_get('yoti_connect_email'),
      'yoti_pem' => array(
        'name' => $name,
        'contents' => $contents,
      ),
    );

    if (self::mockRequests()) {
      $config = array_merge($config, require_once __DIR__ . '/sdk/sample-data/config.php');
    }

    return $config;
  }

  /**
   * Returns Yoti API URL.
   *
   * @return null|string
   *   Yoti API URL
   */
  public static function getLoginUrl() {
    $config = self::getConfig();
    if (empty($config['yoti_app_id'])) {
      return NULL;
    }

    return YotiClient::getLoginUrl($config['yoti_app_id']);
  }

}
