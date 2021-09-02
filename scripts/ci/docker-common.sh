#!/bin/bash
if [ -z ${BUILD_ENV_NAME+x} ];
then
  echo "Please set the BUILD_ENV_NAME variable.";
  exit 1;
fi

dockerComposeCmd="/usr/local/bin/docker-compose -f tests/docker-compose.yml  -p ${BUILD_ENV_NAME}"

