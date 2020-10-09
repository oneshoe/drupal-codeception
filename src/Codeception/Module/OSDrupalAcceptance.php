<?php

namespace Codeception\Module;

use Codeception\Exception\ModuleException;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\Module\OSDrupal\UserInfo;
use Codeception\TestInterface;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Pages\UserLoginPage;
use Pages\UserLogoutPage;

/**
 * Class OSDrupalAcceptance.
 *
 * ### Example
 * #### Example (DrupalAcceptance)
 *     modules:
 *        - DrupalAcceptance.
 *
 * @package Codeception\Module
 */
class OSDrupalAcceptance extends Module {

  private $amAdmin = FALSE;

  /**
   * The username of user 1.
   */
  protected $rootUser;

  /**
   * The password of user 1.
   */
  protected $rootPassword;

  /**
   * Role to use to distinguish test users.
   */
  protected $testRole;

  /**
   * Human friendly name for the tester role.
   */
  protected $testRoleLabel;

  /**
   * OSDrupalAcceptance constructor.
   *
   * @param \Codeception\Lib\ModuleContainer $moduleContainer
   * @param null $config
   */
  public function __construct(ModuleContainer $moduleContainer, $config = NULL) {
    parent::__construct($moduleContainer, $config);

    $this->rootUser = isset($config['rootUser']) ? $config['rootUser'] : 'admin';
    $this->rootPassword = isset($config['rootPassword']) ? $config['rootPassword'] : 'admin';
    $this->testRole = isset($config['testRole']) ? $config['testRole'] : 'codeceptiontester';
    $this->testRoleLabel = isset($config['testRoleLabel']) ? $config['testRoleLabel'] : 'Tester';
  }

  public function _beforeSuite($settings = []) {
    // Create a role to mark users created during testing.
    $output = $this->executeDrushCommand('role:list', ['format' => 'json']);
    $roles = json_decode($output, TRUE);

    if (!isset($roles[$this->testRole])) {
      codecept_debug($this->executeDrushCommand("role:create " . $this->testRole . " " . $this->testRoleLabel));
    }

    // Create users for each role.
    codecept_debug($this->executeDrushCommand('test-users:create'));
  }

  public function _afterSuite($settings = []) {
    $this->executeDrushCommands([
      'test-users:delete-with-role ' . $this->testRole . ' -y' => [],
      'role:delete ' . $this->testRole . ' -y' => [],
    ]);
  }

  public function _after(TestInterface $test) {
    // All sessions will be ended after each test, so we need to record we are
    // no longer admin.
    $this->amAdmin = FALSE;
  }

  /**
   * Whether logged in as admin.
   *
   * @return bool
   */
  public function amAdmin() {
    return $this->amAdmin;
  }

  /**
   * Login as a named user.
   *
   * @param string $name
   *   User name.
   * @param string $password
   *   [Optional] password.
   *
   * @throws \Codeception\Exception\ModuleException
   */
  public function loginAs($name, $password = NULL) {
    $this->clearCookies();
    $webDriver = $this->getWebDriver();

    if (!$webDriver->loadSessionSnapshot($name)) {
      try {
        $currentUrl = $webDriver->grabFromCurrentUrl();
      }
      catch (ModuleException $e) {
        $currentUrl = '';
      }
      $webDriver->amOnPage(UserLoginPage::URL . '?destination=' . $currentUrl);
      $webDriver->submitForm(UserLoginPage::USERLOGIN, [
        'name' => $name,
        'pass' => $password ?: 'password',
      ]);
      $webDriver->saveSessionSnapshot($name);
    }
    $this->amAdmin = ($name === $this->rootUser);
  }

  /**
   * Log out the user.
   *
   * The name passed needs to correspond to the current user, since we will be
   * deleting the session snapshot belonging to the user. Otherwise, the log out
   * would not be complete and logging in would not go through the login form.
   *
   * @param string $name
   *   User name.
   *
   * @throws \Codeception\Exception\ModuleException
   */
  public function logOut($name) {
    $webDriver = $this->getWebDriver();

    try {
      $currentUrl = $webDriver->grabFromCurrentUrl();
    }
    catch (ModuleException $e) {
      $currentUrl = '';
    }

    $webDriver->amOnPage(UserLogoutPage::URL . '?destination=' . $currentUrl);
    $webDriver->deleteSessionSnapshot($name);
    $this->amAdmin = FALSE;
  }

  /**
   * Switch to admin user.
   *
   * @throws \Codeception\Exception\ModuleException
   */
  public function switchToAdmin() {
    $this->getWebDriver()->saveSessionSnapshot('currentUser');
    if (!$this->amAdmin) {
      $this->loginAs($this->rootUser, $this->rootPassword);
    }
  }

  /**
   * Switch back to previous user.
   *
   * @throws \Codeception\Exception\ModuleException
   */
  public function switchBackFromAdmin() {
    if ($this->amAdmin) {
      $this->clearCookies();
      $this->getWebDriver()->loadSessionSnapshot('currentUser');

      $this->amAdmin = FALSE;
    }
  }

  /**
   * Create user with given role.
   *
   * @param string $role
   *   Role.
   *
   * @return \Codeception\Module\OSDrupal\UserInfo
   *   User data.
   */
  public function createTestUser($roles = [], $name = NULL) {
    $password = $this->generateRandomPassword();

    if (is_null($name)) {
      $name = 'test-' . $password;
    }

    $mail = $name . '@localhost.localdomain';

    $this->executeDrushCommand('user:create ' . $name, [
      'password' => $password,
      'mail' => $mail,
    ]);

    $roles = array_merge($roles, [$this->testRole]);
    foreach ($roles as $role) {
      $this->executeDrushCommand("user:role:add $role $name");
    }

    return new UserInfo($name, $password, $mail);
  }

  /**
   * Delete user by given username.
   *
   * @param string $name
   *   Username.
   */
  public function deleteUser($name) {
    // There is no direct way to delete a user, but it is just another entity,
    // so we can use entity:delete instead.
    $userInfo = json_decode($this->executeDrushCommand('user:information ' . $name, ['format' => 'json']), TRUE);
    $userInfo = reset($userInfo);
    $this->executeDrushCommand('entity:delete user ' . $userInfo['uid']);
  }

  /**
   * Wait for the batch process to finish.
   */
  public function waitForBatchProcessToFinish() {
    $I = $this;

    $uri = $I->grabFromCurrentUrl();

    if (strpos($uri, 'batch') !== FALSE) {
      // Wait for the batch process to finish.
      $I->waitForText('Statusbericht', 90);
      $I->dontSee('Foutmelding', 'h2');
    }
  }

  /**
   * Generate a random node title.
   */
  public function generateNodeTitle(): string {
    return strtoupper(bin2hex(openssl_random_pseudo_bytes(10)));
  }

  /**
   * Generate a random password.
   *
   * @param int $length
   *   The length of the password. Defaults to 10.
   *
   * @return string
   *   The password.
   */
  public function generateRandomPassword($length = 3): string {
    $characters = [
      'digits' => '0123456789',
      'upperCaseChars' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
      'lowerCaseChars' => 'abcdefghijklmnopqrstuvwxyz',
    ];
    $random_password = '';
    foreach ($characters as $charSet) {
      $characters_length = strlen($charSet);
      for ($i = 0; $i < $length; $i++) {
        $random_password .= $charSet[random_int(0, $characters_length - 1)];
      }
    }
    return $random_password;
  }

  /**
   * Enter a value in a CKEditor.
   *
   * @param string $elementId
   *   The id of the element (without #).
   * @param string $content
   *   The value to place into the editor.
   *
   * @throws \Codeception\Exception\ModuleException
   * @throws \Facebook\WebDriver\Exception\NoSuchElementException
   * @throws \Facebook\WebDriver\Exception\TimeoutException
   */
  public function fillCkEditorById($elementId, $content) {
    $selector = WebDriverBy::cssSelector('#cke_' . $elementId . ' iframe');
    $webDriver = $this->getWebDriver()->webDriver;
    $webDriver->wait(10, 1000)->until(
      WebDriverExpectedCondition::presenceOfElementLocated($selector)
    );
    $webDriver->executeScript("CKEDITOR.instances['$elementId'].setData('" . json_encode($content) . "');");
  }

  /**
   * Enter a value in a CKEditor.
   *
   * @param string $element_name
   *   The name of the element.
   * @param string $content
   *   The value to place into the editor.
   *
   * @throws \Exception
   */
  public function fillCkEditorByName($element_name, $content) {
    $webDriver = $this->getWebDriver();
    $id = $webDriver->grabAttributeFrom('textarea[name="' . $element_name . '"]', 'data-drupal-selector');
    $this->fillCkEditorById($id, $content);
  }

  /**
   * Execute multiple Drush commands.
   *
   * @param array $commands
   *   An array describing the commands. Keys are the command with any
   *   arguments, values are arrays of options. The options will be
   *   augmented with the proper root directory and uri.
   */
  public function executeDrushCommands($commands) {
    foreach ($commands as $command => $options) {
      $output = $this->executeDrushCommand($command, $options);
      codecept_debug($output);
    }
  }

  /**
   * Execute a Drush command.
   *
   * This is a wrapper around the runDrush() method of the DrupalDrush module.
   * The options will be augmented with the proper root directory and uri.
   *
   * @param string $command
   *   The command to execute. Include any options that do not have a value.
   * @param array $options
   *   Options that have a value. The key is the option, the value is... you
   *   guessed it. Use the full name, as the option will be prepended with --.
   *
   * @return string
   *   The output of the command.
   */
  public function executeDrushCommand($command, array $options = []) {
    $pwd = getcwd();

    /** @var \Codeception\Module\DrupalDrush $drush */
    $drush = $this->getModule('DrupalDrush');

    $options += ['root' => $pwd . '/web', 'uri' => 'http://default'];

    return $drush->runDrush($command, $options);
  }

  /**
   * Delete all cookies for the current webdriver.
   *
   * @throws \Codeception\Exception\ModuleException
   */
  public function clearCookies() {
    $this->getWebDriver()->webDriver->manage()->deleteAllCookies();
  }

  /**
   * Get the webdriver module.
   *
   * @return \Codeception\Module\WebDriver
   *   Webdriver.
   *
   * @throws \Codeception\Exception\ModuleException
   */
  protected function getWebDriver() {
    /** @var \Codeception\Module\WebDriver $webDriver */
    $webDriver = $this->getModule('WebDriver');

    return $webDriver;
  }

}
