<?php

require_once DRUPAL_ROOT . '/sites/settings.global.php';
require_once DRUPAL_ROOT . '/sites/settings.dev.php';

$databases['default']['default'] = array (
  'database' => 'drupal',
  'username' => 'root',
  'password' => 'root',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '',
  'isolation_level' => 'READ COMMITTED',
  'driver' => 'mysql',
  'namespace' => 'Drupal\\mysql\\Driver\\Database\\mysql',
  'autoload' => 'core/modules/mysql\\src\\Driver\\Database\\mysql\\',
);

$settings['disable_captcha'] = TRUE;
$config['symfony_mailer.settings']['default_transport'] = 'null';
