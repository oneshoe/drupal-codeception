#!/bin/bash

# To be run at the top level working directory that holds a checkout of the
# repository named "checkout".

source "$(dirname "$0")/../common.sh"

# Stop on any error.
set -e

log "Create a dump of the database for inspection."
lando db-export "testresults/${dumpBaseName}.sql"

log "Stop Lando."
lando stop

log "Destroy Lando environment."
lando destroy -y
