<?php

const ENVIRONMENT = 'test';

require DRUPAL_ROOT . '/sites/settings.global.php';
require DRUPAL_ROOT . '/sites/settings.dev.php';

$databases['default']['default'] = array (
  'database' => 'example_test',
  'username' => 'mysql',
  'password' => 'mysql',
  'prefix' => '',
  'host' => 'mariadb-11.2',
  'port' => '',
  'isolation_level' => 'READ COMMITTED',
  'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
  'driver' => 'mysql',
  'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
);

$config['symfony_mailer.settings']['default_transport'] = 'null';
