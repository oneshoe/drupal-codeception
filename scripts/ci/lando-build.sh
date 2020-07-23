#!/bin/bash

# To be run at the top level working directory that holds a checkout of the
# repository named "checkout".

source "$(dirname "$0")/../common.sh"

# Stop on any error.
set -e

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

log "Install Drupal."
lando clean-install
