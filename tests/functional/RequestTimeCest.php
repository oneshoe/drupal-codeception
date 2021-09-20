<?php

/**
 * Class RequestTimeCest.
 */
class RequestTimeCest {

  /**
   * Test retrieving the request time from Drupal.
   *
   * @param \FunctionalTester $I
   *   Codeception Actor.
   */
  public function testRequestTime(FunctionalTester $I) {
    $time = \Drupal::time();
    $requestTime = $time->getRequestTime();

    $I->assertIsNumeric($requestTime, 'Request time should be numeric.');
    $I->assertLessThan(5, time() - $requestTime, 'Request time should be within 5 seconds of current time.');
  }

}
