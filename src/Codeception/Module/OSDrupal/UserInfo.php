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
   * UserInfo constructor.
   *
   * @param string $name
   *   The user's name.
   * @param $password
   *   The user's password.
   */
  public function __construct($name, $password) {
    $this->name = $name;
    $this->password = $password;
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

}
