<?php

namespace Helper;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;

/**
 * Class CoverageTrait.
 *
 * We can not use regular Codeception test coverage because it is geared towards
 * coverage for the system under test. Instead we handle coverage manually.
 *
 * @package _support\Helper
 */
trait CoverageTrait {

  protected $coverage;

  protected function startCoverage($directories = [], $files = []) {
    $filter = new Filter;

    if (empty($directories) && empty($files)) {
      $filter->includeDirectory('/app/src');
    }
    else {
      foreach ($directories as $directory) {
        $filter->includeDirectory($directory);
      }
      foreach ($files as $file) {
        $filter->includeFile($file);
      }
    }

    $this->coverage = new CodeCoverage(
      (new Selector)->forLineCoverage($filter),
      $filter
    );

    $this->coverage->start('Drupal CodeCeption');
  }

  protected function stopCoverage() {
    $this->coverage->stop();

    (new HtmlReport)->process($this->coverage, '/app/tests/_output/coverage');
    (new Clover())->process($this->coverage, '/app/tests/_output/coverage/coverage.xml');
  }

}
