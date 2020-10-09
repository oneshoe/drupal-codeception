#!/bin/bash

# To be run at the top level working directory that holds a checkout of the
# repository named "checkout".

# Stop on any error.
set -e

echo "Make test results directory if it does not exist."
mkdir -p testresults

echo "Move to the checkout directory."
cd checkout

if [ -z ${BUILD_ENV_NAME+x} ];
then
  echo "Please set the BUILD_ENV_NAME variable.";
  exit 1;
fi

echo "Set a local Lando configuration with overriden project name."
# awk to convert the project name to lower case. Upper case has proven
# troublesome with Lando.
echo "name: ${BUILD_ENV_NAME}" | awk '{print tolower($0)}' > .lando.local.yml
echo '
services:
  appserver:
    overrides:
      environment:
        ONESHOE_ENV: test' >> .lando.local.yml

echo "Start Lando."
lando start

echo "Copy composer.lock so we know what the status quo is."
cp composer.lock ../testresults/
