<?php

const ENVIRONMENT = 'test';

require_once DRUPAL_ROOT . '/sites/settings.global.php';
require_once DRUPAL_ROOT . '/sites/settings.dev.php';

$databases['default']['default'] = array (
  'database' => 'example_test',
  'username' => 'mysql',
  'password' => 'mysql',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '',
  'isolation_level' => 'READ COMMITTED',
  'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
  'driver' => 'mysql',
  'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
);

$config['symfony_mailer.settings']['default_transport'] = 'null';
