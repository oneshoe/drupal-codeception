#!/bin/bash

# To be run at the top level working directory that holds a checkout of the
# repository named "checkout".

# Stop on any error.
set -e

# Allow a delay to be introduced in order to help Lando starts not happening
# simultaneously.
if [ -n "${START_DELAY+x}" ]; then
  echo "Wait for ${START_DELAY} seconds..."
  sleep "${START_DELAY}"
fi

"$(cd -P -- "$(dirname -- "$0")" && pwd -P)/../setup-environment.sh"
# Running the above script will have moved us to the checkout directory.

cd "$(cd -P -- "$(dirname -- "$0")" && pwd -P)/../../.."

echo "Run acceptance tests."
lando codecept run unit --env=ci --xml --no-interaction --steps --debug