#!/bin/bash


VERSION=$1



if [ -z ${VERSION} ]
then
echo "Usage : $0 version"
echo "Create a production package in ./build/ directory"
exit;
fi

TARGET=build/package/

rm -rf ${TARGET}
mkdir -p ${TARGET}
sed -e "s/canary$/${VERSION}/" docker/docker-compose.yml >  ${TARGET}/docker-compose.yml


