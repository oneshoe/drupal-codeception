<?php

class AdminSwitchCest
{
  public function switchToAdminFromAnonymous(AcceptanceTester $I)
  {
    $I->assertFalse($I->amAdmin());
    $I->switchToAdmin();
    $I->assertTrue($I->amAdmin());
    $I->switchBackFromAdmin();
    $I->assertFalse($I->amAdmin());
  }
}
