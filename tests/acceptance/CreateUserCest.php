<?php

class CreateUserCest
{
  public function createUser(AcceptanceTester $I)
  {
    $user = $I->createTestUser();
    $I->amOnPage('/');
    $I->loginAs($user->getName(), $user->getPassword());
    $I->amOnPage('/user');
    $I->see($user->getName());
  }

}
