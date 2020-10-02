<?php
namespace Helper;

use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlReport;

// We can not use regular CodeCeption test coverage because it is geared towards
// coverage for the system under test. Instead wehandle coverage manually.
class Acceptance extends \Codeception\Module
{
  protected $coverage;

  public function _beforeSuite($settings = []) {
    $filter = new Filter;
    $filter->includeDirectory('/app/src');

    $this->coverage = new CodeCoverage(
      (new Selector)->forLineCoverage($filter),
      $filter
    );

    $this->coverage->start('Drupal CodeCeption');
  }

  public function _afterSuite() {
    $this->coverage->stop();


    (new HtmlReport)->process($this->coverage, '/app/tests/_output/coverage');
    (new Clover())->process($this->coverage, '/app/tests/_output/coverage/coverage.xml');
  }
}
