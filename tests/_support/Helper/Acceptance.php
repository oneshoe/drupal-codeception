<?php

namespace Helper;

class Acceptance extends \Codeception\Module {

  use CoverageTrait;

  public function _beforeSuite($settings = []) {
    $this->startCoverage();
  }

  public function _afterSuite() {
    $this->stopCoverage();
  }

}
