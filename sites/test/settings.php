<?php

define('ENVIRONMENT', 'test');

require_once DRUPAL_ROOT . '/sites/settings.php';
require_once DRUPAL_ROOT . '/sites/settings.dev.php';

$databases['default']['default'] = array (
  'database' => 'example_test',
  'username' => 'mysql',
  'password' => 'mysql',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);
