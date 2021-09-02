#!/bin/bash

# To be run at the top level working directory that holds a checkout of the
# repository named "checkout".

source "$(dirname "$0")/../docker-common.sh"

# Stop on any error.
set -e

"$(cd -P -- "$(dirname -- "$0")" && pwd -P)/../setup-environment.sh"

cd checkout

${dockerComposeCmd} exec -T database timeout 50 bash -c "while !(mysqladmin ping) 2>/dev/null
do
   sleep 3
   echo 'Waiting for mysql ...'
done
"

echo "Install Drupal."
${dockerComposeCmd} exec -T php bash scripts/clean-install.sh

echo "Make everything writable."
# Especially files directory.
sudo chown -R bamboo:bamboo .
chmod a+w -R *

echo "Set logging to verbose."
${dockerComposeCmd} exec -T php bash -c "cd web && ../vendor/bin/drush cset system.logging error_level verbose -y"

echo "Run acceptance tests."
${dockerComposeCmd} exec -T php vendor/bin/codecept run acceptance --env=ci --xml --no-interaction --steps --debug
