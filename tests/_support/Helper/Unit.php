<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends \Codeception\Module {

  use CoverageTrait;

  public function _beforeSuite($settings = []) {
    $this->startCoverage();
  }

  public function _afterSuite() {
    $this->stopCoverage();
  }

}
