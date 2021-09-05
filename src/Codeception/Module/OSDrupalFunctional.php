<?php

namespace Codeception\Module;

use Codeception\Configuration;
use Codeception\Lib\ModuleContainer;
use Codeception\Module;
use Codeception\TestDrupalKernel;
use Codeception\TestInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\PhpStorage\PhpStorageFactory;
use Exception;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OSDrupalFunctional.
 *
 * @package Codeception\Module
 */
class OSDrupalFunctional extends Module
{
  /**
   * Highwatermark for the database logging.
   *
   * @var int
   */
  protected $watchdog;

  /**
   * Drupal8Module constructor.
   *
   * @param ModuleContainer $container
   * @param null $config
   */
  public function __construct(ModuleContainer $container, $config = null)
  {
    $new_config = array_merge(
      [
        'environment' => 'test',
        'app_root' => Configuration::projectDir() . 'web',
        'site_path' => 'sites/default',
        'clear_caches' => false,
      ],
      (array)$config
    );

    parent::__construct($container, $new_config);
  }

  public function _initialize()
  {
    $site_path = $this->config['site_path'];
    $app_root = realpath($this->config['app_root']);
    $environment = $this->config['environment'];

    // Bootstrap a bare minimum Kernel so we can interact with Drupal.
    $class_loader = require $app_root . '/autoload.php';
    $kernel = new TestDrupalKernel($environment, $class_loader, true, $app_root);
    // Drupal still doesn't work quite right when you don't.
    chdir($app_root);
    $kernel->bootTestEnvironment($site_path);
  }

  /**
   * Create a cleanly booted environemnt for every test.
   *
   * @param TestInterface $test
   */
  public function _before(TestInterface $test)
  {
    $app_root = realpath($this->config['app_root']);
    $class_loader = require $app_root . '/autoload.php';
    $kernel = new TestDrupalKernel($this->config['environment'], $class_loader, false, $app_root);
    $kernel->setSitePath($this->config['site_path']);
    $request = Request::create('/');
    $kernel->prepareLegacyRequest($request);

    // Clean up everything, slow but thorough.
    if ($this->config['clear_caches']) {
      $module_handler = \Drupal::moduleHandler();
      // Flush all persistent caches.
      $module_handler->invokeAll('cache_flush');
      foreach (Cache::getBins() as $cache_backend) {
        $cache_backend->deleteAll();
      }

      // Reset all static caches.
      drupal_static_reset();

      // Wipe the Twig PHP Storage cache.
      PhpStorageFactory::get('twig')->deleteAll();
    }

    $this->watchdog = \Drupal::database()
      ->query('SELECT MAX(wid) FROM {watchdog}')
      ->fetchField();
    \Drupal::getContainer()->get('path.alias_manager')->cacheClear();
    $this->startTransaction();
  }

  /**
   * Rollback transaction.
   */
  public function _after(TestInterface $test) {
    $this->logWatchdog();
    $this->rollbackTransaction();
  }

  /**
   * Rollback transaction.
   */
  public function _failed(TestInterface $test, $fail) {
    $this->logWatchdog();
    $this->rollbackTransaction();
  }

  /**
   * Setup Test environment.
   *
   * @param array $settings
   */
  public function _beforeSuite($settings = [])
  {
    if ($this->config['create_users']) {
      $this->scaffoldTestUsers();
    }
  }

  /**
   * Tear down after tests.
   */
  public function _afterSuite()
  {
    if ($this->config['destroy_users']) {
      $this->tearDownTestUsers();
    }
  }

  /**
   * Start a drupal database transaction.
   *
   * @param string $name
   *   [Optional] name for the transaction savepoint.
   */
  public function startTransaction($name = 'CodeceptionDrupal') {
    $this->transaction = Database::getConnection()->startTransaction($name);
  }

  /**
   * Rollback the drupal database transaction.
   */
  public function rollbackTransaction() {
    if ($this->transaction !== NULL) {
      try {
        $this->transaction->rollback();
      }
      catch (\Exception $e) {
        watchdog_exception('functional_tester', $e);
      }

      $this->transaction = NULL;
    }
  }

  /**
   * Output all the watchdog entries made during this test run.
   *
   * To console when ran with --debug
   * And to a .drupallog file in the output directory.
   */
  protected function logWatchdog() {
    try {
      $query = \Drupal::database()->select('watchdog', 'w')
        ->fields('w');
      $query->condition('wid', $this->watchdog, '>');
      $rows = $query->execute();
    }
    catch (InvalidQueryException $exception) {
      codecept_debug($exception);
    }
    if (!empty($rows)) {
      $log = '';
      foreach ($rows as $row) {
        $arguments = unserialize($row->variables, ['allowed_classes' => FALSE]);
        $message = new FormattableMarkup($row->message, $arguments);
        $message = strip_tags($message);
        $message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');
        $levels = RfcLogLevel::getLevels();
        /** @var \Drupal\Core\StringTranslation\TranslatableMarkup $severity */
        $severity = $levels[$row->severity];
        $items = [
          date('Y-m-d\TH:i:s', $row->timestamp),
          $severity->getUntranslatedString(),
          $row->type,
          $message,
        ];
        $log .= implode(' ', $items) . PHP_EOL;
      }
      if (!empty($log)) {
        $file = codecept_output_dir() . get_class($this) . '.drupallog';
        $content = '[[[ DRUPAL WATCHDOG ]]]' . PHP_EOL;
        $trimmarker = PHP_EOL . '>>>>rest of string cut off. Full log: ' . $file;
        $content .= mb_strimwidth($log, 0, 500, $trimmarker);
        codecept_debug($content);
        file_put_contents($file, $log, FILE_APPEND);
      }
    }
  }

}
