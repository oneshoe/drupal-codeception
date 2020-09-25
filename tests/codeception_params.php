<?php

/**
 * @file
 * Codeception params.
 *
 * Dynamically try to determine the urls and database settings of the current
 * environment.
 */

$params = [];

$settingsfile = 'web/sites/default/settings.php';
$propertiesfile = '../conf/tos.properties';
$landoproperties = json_decode(getenv('LANDO_INFO'), TRUE);

$params['CHROMEDRIVER_HOST'] = getenv('CHROMEDRIVER_HOST') ?: '127.0.0.1';

if (file_exists($propertiesfile)) {
  $tos = parse_ini_file($propertiesfile);
  $params['DB_DSN'] = $tos['pdo.driver'] . ':host=' . $tos['tos.site.dbhost'] . ';dbname=' . $tos['tos.site.dbname'];
  $params['DB_USER'] = $tos['tos.site.dbuname'];
  $params['DB_PASSWORD'] = $tos['tos.site.dbpass'];
  $params['WEBDRIVER_URL'] = 'http://' . $tos['tos.site.hostname'];
}
elseif (!empty($landoproperties)) {
  print_r($landoproperties);
  $database = $landoproperties['database'];
  $creds = $database['creds'];
  $driver = $database['type'] === 'postgres' ? 'pgsql' : 'mysql';
  $params['DB_DSN'] = $driver . ':host=' . $database['hostnames'][0] . ';dbname=' . $creds['database'];
  $params['DB_USER'] = $creds['user'];
  $params['DB_PASSWORD'] = $creds['password'];
  $params['WEBDRIVER_URL'] = end($landoproperties['appserver']['urls']);
  $params['CHROMEDRIVER_HOST'] = 'chromedriver';
}
elseif (file_exists($settingsfile)) {
  $databases = [];
  // Read settings from the settings file.
  @include $settingsfile;
  if (isset($databases['default']['default'])) {
    foreach (['database', 'username', 'password', 'host', 'port', 'driver'] as $field) {
      if (isset($databases['default']['default'][$field])) {
        $$field = $databases['default']['default'][$field];
      }
    }
  }
}

echo "Using following parameters:\n";
echo "===========================\n";
foreach ($params as $key => $value) {
  echo "$key: $value\n";
}
echo "===========================\n";
echo "\n";

return $params;
