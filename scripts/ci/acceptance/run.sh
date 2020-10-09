#!/bin/bash

# To be run at the top level working directory that holds a checkout of the
# repository named "checkout".

# Stop on any error.
set -e

"$(cd -P -- "$(dirname -- "$0")" && pwd -P)/../setup-environment.sh"
# Running the above script will have moved us to the checkout directory.

cd "$(cd -P -- "$(dirname -- "$0")" && pwd -P)/../../.."

echo "Install composer dependencies."
lando composer install

echo "Install Drupal."
lando clean-install

echo "Set logging to verbose."
lando drush cset system.logging error_level verbose -y

echo "Run acceptance tests."
lando codecept run acceptance --env=ci --xml --no-interaction --steps --debug
