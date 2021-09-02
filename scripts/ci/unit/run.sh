#!/bin/bash

# To be run at the top level working directory that holds a checkout of the
# repository named "checkout".

source "$(dirname "$0")/../docker-common.sh"

# Stop on any error.
set -e

"$(cd -P -- "$(dirname -- "$0")" && pwd -P)/../setup-environment.sh"

cd checkout

echo "Run unit tests."
${dockerComposeCmd} exec -T php vendor/bin/codecept run unit --env=ci --xml --no-interaction --steps --debug
