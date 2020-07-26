#!/bin/bash

# To be run at the top level working directory that holds a checkout of the
# repository named "checkout".

# Stop on any error.
set -e

echo "Take ownership."
sudo chown -R bamboo:bamboo .

echo "Save test results."
mkdir -p testresults/coverage
cp -r checkout/tests/_output/* testresults/
if [ -f testresults/coverage.xml ]; then
  cp testresults/coverage.xml testresults/coverage/
else
  touch testresults/coverage/coverage.xml
fi

cd checkout

if [ -z ${DUMP_NAME+x} ]; then
  echo "Skipping database dump. Set DUMP_NAME to create a dump."
else
  echo "Create a dump of the database for inspection."
  lando db-export "${DB_DUMP_NAME}.sql"
  mv "${DB_DUMP_NAME}.sql" ../testresults
fi

echo "Save the Lando logs."
lando logs -t > ../testresults/lando.log

log "Stop Lando."
lando stop

log "Destroy Lando environment."
lando destroy -y
