#!/bin/bash

# To be run at the top level working directory that holds a checkout of the
# repository named "checkout".

source "$(dirname "$0")/docker-common.sh"

# Do not stop on any error, we at the very least need to shut down the docker
# environment.

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

cd checkout || exit 2

if [ -z ${DB_DUMP_NAME+x} ]; then
  echo "Skipping database dump. Set DB_DUMP_NAME to create a dump."
else
  echo "Create a dump of the database for inspection."
  ${dockerComposeCmd} exec -T php drush sql-dump --gzip --result-file=../${DB_DUMP_NAME}.sql
  mv "${DB_DUMP_NAME}.sql.gz" ../testresults
fi

echo "Save the Docker logs."
${dockerComposeCmd} logs --no-color -t > ../testresults/docker.log

echo "Stop Docker environment."
${dockerComposeCmd} down --rmi local -v --remove-orphans
