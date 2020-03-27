<?php

namespace Codeception\Module;

use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\Util\Drupal\FormField;
use Codeception\Util\Drupal\ParagraphFormField;
use Codeception\Util\IdentifiableFormFieldInterface;
use Facebook\WebDriver\Remote\RemoteWebDriver;

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

  protected $nodes = [];
  private $amAdmin = FALSE;

  /**
   * The username of user 0.
   */
  const ROOT_USER = 'root';

  /**
   * The password of user 0.
   */
  const ROOT_PASS = 'rootpass';

  /**
   * Login with a role.
   *
   * @param string $name
   *   User name.
   * @param string $password
   *   [Optional] password.
   */
  public function loginAs($name, $password = NULL) {
    $I = $this;
    $I->resizeWindow(800, 600);
    $I->clearCookies();
    if (!$I->loadSessionSnapshot($name)) {
      try {
        $currentUrl = $I->grabFromCurrentUrl();
      }
      catch (ModuleException $e) {
        $currentUrl = '';
      }
      $I->amOnPage('/user/login?destination=' . $currentUrl);
      $I->submitForm(UserLoginPage::USERLOGIN, [
        'name' => $name,
        'pass' => $password ?: 'password',
      ]);
      $I->see($name, UserLoginPage::USERNAME);
      $I->saveSessionSnapshot($name);
    }
    $I->amAdmin = ($name === self::ROOT_USER);
  }

  /**
   * Switch to admin user.
   */
  private function switchToAdmin() {
    $I = $this;
    $I->saveSessionSnapshot('currentUser');
    if (!$I->amAdmin) {
      $I->loginAs(self::ROOT_USER, self::ROOT_PASS);
      $I->amAdmin = TRUE;
    }
  }

  /**
   * Switch back to previous user.
   */
  private function switchBackFromAdmin() {
    $I = $this;
    if ($I->amAdmin) {
      $I->clearCookies();
      $this->loadSessionSnapshot('currentUser');

      $I->amAdmin = FALSE;
    }
  }

  /**
   * Delete all the nodes created in this tester.
   */
  public function cleanupNodes() {
    $I = $this;
    $I->switchToAdmin();
    if (!empty($this->nodes)) {
      foreach (array_keys($this->nodes) as $nid) {
        $I->amOnPage('node/' . $nid . '/delete');
        $I->click('Verwijderen');
      }
      $this->nodes = [];
    }
    $I->switchBackFromAdmin();
  }

  /**
   * Create user with given role.
   *
   * @param string $role
   *   Role.
   *
   * @return array
   *   User data.
   */
  public function createTestUser($role = NULL, $name = NULL): array {
    $I = $this;
    $password = $I->generateRandomPassword();
    $I->loginAs(self::ROOT_USER, self::ROOT_PASS);
    $I->amOnPage('admin/people/create');
    if (is_null($name)) {
      $name = 'test-' . $password;
    }
    $I->fillField('#edit-name', $name);
    $I->fillField('#edit-mail', $name . '@localhost.localdomain');
    $I->fillField('#edit-pass-pass1', $password);
    $I->fillField('#edit-pass-pass2', $password);
    $I->checkOption(ucfirst(Acceptance::TEST_ROLE));
    if ($role) {
      $I->checkOption(ucfirst($role));
    }
    // This one is a bit strange, it doesn't have a proper ID.
    $I->fillField('Your current logout threshold', 3600);
    $I->checkOption(UserRegisterPage::LEGALAGEFIELD);
    $I->checkOption(UserRegisterPage::LEGALTERMSFIELD);
    $I->click('#edit-submit');

    return [
      'username' => $name,
      'password' => $password,
    ];
  }

  /**
   * Delete user by given username.
   *
   * @param string $username
   *   Username.
   *
   * @deprecated Use deleteUser instead.
   */
  public function deleteTestUser($username) {
    $this->deleteUser($username);
  }

  /**
   * Delete user by given username.
   *
   * @param string $username
   *   Username.
   */
  public function deleteUser($username) {
    $I = $this;
    $I->loginAs(self::ROOT_USER, self::ROOT_PASS);
    $I->amOnPage('ervaringen-van-anderen/personen/' . $username . '/cancel');
    $I->selectOption('#edit-user-cancel-method-user-cancel-block', 'user_cancel_block');
    $I->uncheckOption('#edit-user-cancel-confirm');
    $I->click('#edit-submit');
    $I->waitForBatchProcessToFinish();
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
   * @param string $element_id
   *   The id of the element without #.
   * @param string $content
   *   The value to place into the editor.
   *
   * @throws \Exception
   */
  public function fillCkEditorById($element_id, $content) {
    $selector = WebDriverBy::cssSelector('#cke_' . $element_id . ' iframe');
    /** @var \Facebook\WebDriver\Remote\RemoteWebDriver $webDriver */
    $webDriver = $this->getWebDriver();
    $webDriver->wait(10, 1000)->until(
      WebDriverExpectedCondition::presenceOfElementLocated($selector)
    );
    $webDriver->executeScript("CKEDITOR.instances['$element_id'].setData('" . addslashes($content) . "');");
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
    // NOTE: This uses a different mechanism from fillCkEditorById(). The
    // fillRteEditor() method has proved quite unstable, maybe the JS-based
    // approach in fillCkEditorById() is more succesful. You may want to try and
    // port this to the same mechanism if you find yourself needing this.
    $selector = WebDriverBy::cssSelector('textarea[name="' . $element_name . '"] + .cke iframe');
    $this->fillRteEditor($selector, $content);
  }

  /**
   * Enter a value in a TinyMceEditor.
   *
   * @param string $id
   *   The id of the element.
   * @param string $content
   *   The value to place into the editor.
   *
   * @throws \Exception
   */
  public function fillTinyMceEditorById($id, $content) {
    $this->fillTinyMceEditor('id', $id, $content);
  }

  /**
   * Enter a value in a TinyMceEditor.
   *
   * @param string $name
   *   The name of the element.
   * @param string $content
   *   The value to place into the editor.
   *
   * @throws \Exception
   */
  public function fillTinyMceEditorByName($name, $content) {
    $this->fillTinyMceEditor('name', $name, $content);
  }

  /**
   * Enter a value in a TinyMceEditor.
   *
   * @param string $attribute
   *   The attribute to check.
   * @param string $value
   *   The value to match the attribute on.
   * @param string $content
   *   The value to place into the editor.
   *
   * @throws \Exception
   */
  private function fillTinyMceEditor($attribute, $value, $content) {
    $xpath = '//textarea[@' . $attribute . '=\'' . $value . '\']/../div[contains(@class, \'mce-tinymce\')]//iframe';
    $selector = WebDriverBy::xpath($xpath);
    $this->fillRteEditor($selector, $content);
  }

  /**
   * Enter a value in a richt text editor.
   *
   * @param \Facebook\WebDriver\WebDriverBy $selector
   *   The selector to use.
   * @param string $content
   *   The value to place into the editor.
   *
   * @throws \Exception
   */
  private function fillRteEditor(WebDriverBy $selector, $content) {
    /** @var \Facebook\WebDriver\Remote\RemoteWebDriver $webDriver */
    $webDriver = $this->getWebDriver();
    $webDriver->wait(10, 1000)->until(
      WebDriverExpectedCondition::presenceOfElementLocated($selector)
    );
    $frame = $webDriver->findElement($selector);
    $webDriver->switchTo()->frame($frame);

    $script = 'arguments[0].innerHTML = "' . addslashes($content) . '"';
    $by = WebDriverBy::tagName('body');
    $remoteWebElement = $webDriver->findElement($by);
    $webDriver->executeScript($script, [$remoteWebElement]);
    // Wait for a little bit, to make sure the content has been set.
    // Not sure if this works, but these calls keep failing once every few
    // tests.
    $webDriver->wait(1);

    $webDriver->switchTo()->defaultContent();
  }

}
