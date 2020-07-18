<?php


namespace Codeception\Module\OSDrupal;

/**
 * Class UserInfo.
 *
 * Class to hold information about a user account.
 *
 * @package Codeception\Module\OSDrupal
 */
class UserInfo {

  /**
   * The name of the user.
   *
   * @var string
   */
  private $name;

  /**
   * The user's password.
   *
   * @var string
   */
  private $password;

  /**
   * The user's email address.
   *
   * @var string
   */
  private $mail;

  /**
   * UserInfo constructor.
   *
   * @param string $name
   *   The user's name.
   * @param $password
   *   The user's password.
   * @param $mail
   *   The user's email address.
   */
  public function __construct($name, $password, $mail) {
    $this->name = $name;
    $this->password = $password;
    $this->mail = $mail;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param string $name
   */
  public function setName($name) {
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getPassword() {
    return $this->password;
  }

  /**
   * @param string $password
   */
  public function setPassword($password) {
    $this->password = $password;
  }

  /**
   * @return string
   */
  public function getMail() {
    return $this->mail;
  }

  /**
   * @param string $mail
   */
  public function setMail($mail) {
    $this->mail = $mail;
  }

}
