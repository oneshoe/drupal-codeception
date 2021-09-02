#!/bin/bash

# To be run at the top level working directory that holds a checkout of the
# repository named "checkout".

source "$(dirname "$0")/docker-common.sh"

# Stop on any error.
set -e

echo "Make test results directory if it does not exist."
mkdir -p testresults

echo "Allow everyone to write to everything."
chmod a+w -R * || true

echo "Move to the checkout directory."
cd checkout

echo "Start Docker Compose."
${dockerComposeCmd} up -d

echo "Install composer dependencies."
${dockerComposeCmd} exec -T php composer install

echo "Copy composer.lock so we know what the status quo is."
cp composer.lock ../testresults/
