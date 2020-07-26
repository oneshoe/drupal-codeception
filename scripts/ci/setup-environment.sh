#!/bin/bash

# To be run at the top level working directory that holds a checkout of the
# repository named "checkout".

# Stop on any error.
set -e

echo "Make test results directory if it does not exist."
mkdir -p testresults

echo "Move to the checkout directory."
cd checkout

log "Set a local Lando configuration with overriden project name."
# awk to convert the project name to lower case. Upper case has proven
# troublesome with Lando.
echo "name: ${landoEnvironmentName}" | awk '{print tolower($0)}' > .lando.local.yml
echo '
services:
  appserver:
    overrides:
      environment:
        ONESHOE_ENV: test' >> .lando.local.yml

log "Start Lando."
lando start

log "Install composer dependencies."
lando composer install

echo "Install Drupal."
lando clean-install

echo "Set logging to verbose."
lando -vvv drush cset system.logging error_level verbose

echo "Copy composer.lock so we know what the status quo is."
cp composer.lock ../testresults/
