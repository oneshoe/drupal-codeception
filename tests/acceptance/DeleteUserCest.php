<?php

class DeleteUserCest
{
  public function deleteUser(AcceptanceTester $I)
  {
    $I->amGoingTo('create a user, log in as the user, log out, and then delete it.');
    $user = $I->createTestUser();
    $I->amOnPage('/');
    $I->loginAs($user->getName(), $user->getPassword());
    $I->amOnPage('/user');
    $I->see($user->getName());
    $I->amOnPage('/');
    $I->logOut($user->getName());
    $I->expect('to be able to delete the user while not logged in.');
    $I->deleteUser($user->getName());
    $I->loginAs($user->getName(), $user->getPassword());
    $I->see('Unrecognized username or password.');
  }

}
