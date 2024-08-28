<?php

const ENVIRONMENT = 'dev';

require DRUPAL_ROOT . '/sites/settings.global.php';
require DRUPAL_ROOT . '/sites/settings.dev.php';

$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => 'root',
  'password' => 'root',
  'prefix' => '',
  'host' => 'mariadb-11.2',
  'port' => '',
  'isolation_level' => 'READ COMMITTED',
  'driver' => 'mysql',
  'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
  'autoload' => 'core/modules/mysql/src/Driver/Database/mysql/',
);

$settings['disable_captcha'] = TRUE;
$config['symfony_mailer.settings']['default_transport'] = 'null';
