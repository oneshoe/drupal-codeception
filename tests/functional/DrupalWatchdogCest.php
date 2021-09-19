<?php

/**
 * Class DrupalWatchdogCest.
 */
class DrupalWatchdogCest {

  /**
   * Test the watchdog recording functionality.
   *
   * @param \FunctionalTester $I
   *   Codeception Actor.
   */
  public function logWatchdogMessages(FunctionalTester $I) {
    $logger = \Drupal::logger('drupal_codeception');
    $logger->notice('This is a notice message.');
    $logger->info('This is an info message.');
    $logger->warning('This is a warning message.');
    $logger->error('This is a warning message.');
    $logger->alert('This is an alert message.');
    $logger->critical('This is a critical message.');
  }

}
