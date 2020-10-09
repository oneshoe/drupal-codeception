<?php

use Codeception\Module\OSDrupal\UserInfo;

/**
 * Class UserInfoCest.
 */
class UserInfoCest {

  /**
   * Test if all setters and getters of the UserInfo class work.
   *
   * @param \UnitTester $I
   *   Codeception Actor.
   */
  public function testUserInfo(UnitTester $I) {
    $name = 'username';
    $password = random_bytes(8);
    $mail = 'username@example.com';

    $userInfo = new UserInfo($name, $password, $mail);
    $I->assertEquals($name, $userInfo->getName(), 'Method getName() should return the value set in the constructor.');
    $I->assertEquals($password, $userInfo->getPassword(), 'Method getPassword() should return the value set in the constructor.');
    $I->assertEquals($mail, $userInfo->getMail(), 'Method getMail() should return the value set in the constructor.');

    $name = 'other username';
    $userInfo->setName($name);
    $I->assertEquals($name, $userInfo->getName(), 'Method getName() should return the value set by setName().');
    $I->assertEquals($password, $userInfo->getPassword(), 'The value returned by getPassword() should not be influenced by the value set by setName().');
    $I->assertEquals($mail, $userInfo->getMail(), 'The value returned by getMail() should not be influenced by the value set by setName().');

    $password = random_bytes(9);
    $userInfo->setPassword($password);
    $I->assertEquals($password, $userInfo->getPassword(), 'Method getPassword() should return the value set by setPassword().');
    $I->assertEquals($name, $userInfo->getName(), 'The value returned by getName() should not be influenced by the value set by setPassword().');
    $I->assertEquals($mail, $userInfo->getMail(), 'The value returned by getMail() should not be influenced by the value set by setPassword().');

    $mail = 'othergal@example.com';
    $userInfo->setMail($mail);
    $I->assertEquals($mail, $userInfo->getMail(), 'Method getMail() should return the value set by setMail().');
    $I->assertEquals($name, $userInfo->getName(), 'The value returned by getName() should not be influenced by the value set by setMail().');
    $I->assertEquals($password, $userInfo->getPassword(), 'The value returned by getPassword() should not be influenced by the value set by setMail().');
  }

}
