<?php

/**
 * @file
 * Codeception params.
 *
 * Dynamically try to determine the urls and database settings of the current
 * environment.
 */

$params = [];

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
  $database = $landoproperties['database'];
  $creds = $database['creds'];
  $driver = $database['type'] === 'postgres' ? 'pgsql' : 'mysql';
  $params['DB_DSN'] = $driver . ':host=' . $database['hostnames'][0] . ';dbname=' . $creds['database'];
  $params['DB_USER'] = $creds['user'];
  $params['DB_PASSWORD'] = $creds['password'];
  $params['WEBDRIVER_URL'] = 'http://appserver/';
  $params['CHROMEDRIVER_HOST'] = 'chromedriver';
}
else {
  // Otherwise, use the values required for Docker.
  $params['DB_DSN'] = 'mysql:host=database;dbname=drupal8';
  $params['DB_USER'] = 'drupal8';
  $params['DB_PASSWORD'] = 'drupal8';
  $params['WEBDRIVER_URL'] = 'http://apache/';
}

echo "Using following parameters:\n";
echo "===========================\n";
foreach ($params as $key => $value) {
  echo "$key: $value\n";
}
echo "===========================\n";
echo "\n";

return $params;
