#!/bin/bash

# To be run at the top level working directory that holds a checkout of the
# repository named "checkout".

source "$(dirname "$0")/../common.sh"

# Stop on any error.
set -e

# Be more verbose about the commands being executed.
set -x

log "Make test results directory if it does not exist."
mkdir -p testresults

log "Set logging to verbose."
lando -vvv drush cset system.logging error_level verbose

# When using this as the base line for a project test this can be removed.
# Foundation does not contain a composer.lock in the project, which makes it
# interesting to see what it is.
log "Copy composer.lock so we know what the status quo is."
cp composer.lock testresults/

log "Check if we see the frontpage for an installed Drupal."
url=$(lando info -s appserver --path [0]['urls'][0] | tr -d \')
log "Checking ${url}..."
curl -k "${url}" -o testresults/curloutput.html
grep -q '<meta name="Generator" content="Drupal' testresults/curloutput.html || exit 1
