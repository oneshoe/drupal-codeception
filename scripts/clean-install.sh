#!/usr/bin/env bash

echo -e "\e[36m--------- Performing clean install ---------\e[39m"

echo "Go to the project root."
cd "$(cd -P -- "$(dirname -- "$0")" && pwd -P)/.." || exit 1;

echo "Clean up drupal files dir."
rm -rf web/sites/default/files/*

echo "Go to the drupal root."
cd web || exit 1;

echo "Install drupal."
../vendor/bin/drush si --site-name "Drupal Codeception" --db-url mysql://drupal8:drupal8@database/drupal8 --account-name root --account-pass supersecret standard  -y

echo "Enable test users module."
../vendor/bin/drush en test_users -y

echo -e "\e[36m========= End of clean install =========\e[39m"
