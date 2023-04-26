#!/usr/bin/env sh

usage () { echo "Usage : $0 -r <registry> -n <registry namespace> -i <docker image> -u <user> -p <password>"; }

# parse args
while getopts "r:n:i:u:p:" opts; do
   case ${opts} in
      r) REGISTRY=${OPTARG} ;;
      n) REGISTRY_NAMESPACE=${OPTARG} ;;
      i) IMAGE_NAME=${OPTARG} ;;
      u) USER=${OPTARG} ;;
      p) PASSWORD=${OPTARG} ;;
      *) usage; exit;;
   esac
done

# those args must be not null
if [ ! "$REGISTRY" ] || [ ! "$REGISTRY_NAMESPACE" ] || [ ! "$IMAGE_NAME" ] || [ ! "$USER" ] || [ ! "$PASSWORD" ]
then
    usage
    exit 1
fi

docker login $REGISTRY
docker tag IMAGE_NAME $REGISTRY/$REGISTRY_NAMESPACE/$IMAGE_NAME
docker push $REGISTRY/$REGISTRY_NAMESPACE/$IMAGE_NAME
