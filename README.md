Installation:

    composer create-project xandeadx/drupal-starter --stability=dev --no-dev --remove-vcs
    vendor/bin/drush site-install minimal --db-url=mysql://root:root@localhost/drupal --account-pass=admin
    vendor/bin/drush config-import --partial --source=sites/default/files/config/sync
