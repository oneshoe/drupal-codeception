<?php

class BatchProcessCest
{
  public function runBatchProcess(AcceptanceTester $I)
  {
    $I->amGoingTo('check for updates in order to have a batch process to check.');
    $I->switchToAdmin();
    $I->amOnPage('/admin/reports/updates');
    $I->click('Check manually');
    // The update check will complain about not being able to check one of the
    // modules so we have to skip the error check.
    $I->waitForBatchProcessToFinish('Status message', '');
    $I->see('Checked available update data');
  }

}
