<?php

require_once DRUPAL_ROOT . '/sites/settings.php';

/*$databases['default']['default'] = array (
  'database' => '',
  'username' => '',
  'password' => '',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
  'driver' => 'mysql',
);*/

if (file_exists($local_settings_file = DRUPAL_ROOT . '/' . $site_path . '/settings.local.php')) {
  include $local_settings_file;
}
