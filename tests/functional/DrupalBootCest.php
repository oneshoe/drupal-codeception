<?php

/**
 * Class DrupalBootCest.
 */
class DrupalBootCest {

  /**
   * Test using a drupal function.
   *
   * @param \FunctionalTester $I
   *   Codeception Actor.
   */
  public function tryToLoadHomepage(FunctionalTester $I) {
    $I->wantTo('see if the system module is there.');
    $ok = \Drupal::moduleHandler()->moduleExists('system');
    $I->assertTrue($ok, 'The system module should be present.');
  }

}
