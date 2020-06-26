<?php

class LoginAsCest
{
  public function loginAs(AcceptanceTester $I)
  {
    $I->loginAs('Authenticated user');
    $I->see('Authenticated user');
  }
}
