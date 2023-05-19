#!/usr/bin/env bash

if [[ -z "$CONTAINER_UID" ]]; then
    export CONTAINER_UID="application"
fi

if [ -f "$WORKDIR/VERSION" ] ; then
    echo "export VERSION=$(cat "$WORKDIR/VERSION")" >> ~/.bashrc
fi

set -o pipefail  # trace ERR through pipes
set -o errtrace  # trace ERR through 'time command' and other functions
set -o nounset   ## set -u : exit the script if you try to use an uninitialised variable
set -o errexit   ## set -e : exit the script if any statement returns a non-true return value

# auto elevate privileges (if container is not started as root)
if [[ "$UID" -ne 0 ]]; then
    export CONTAINER_UID="$UID"
    exec gosu root "$0" "$@"
fi
# remove suid bit on gosu
chmod -s /sbin/gosu

trap 'echo sigterm ; exit' SIGTERM
trap 'echo sigkill ; exit' SIGKILL

# sanitize input and set task
TASK="$(echo $1| sed 's/[^-_a-zA-Z0-9]*//g')"

source /opt/docker/bin/config.sh

createDockerStdoutStderr

if [[ "$UID" -eq 0 ]]; then
    # Only run provision if user is root

    if [ "$TASK" == "supervisord" -o "$TASK" == "noop" ]; then
        # Visible provisioning
        runProvisionEntrypoint
    else
        # Hidden provisioning
        runProvisionEntrypoint  > /dev/null
    fi
fi

#############################
## LARAVEL
#############################
php $WORKDIR/artisan migrate --force
php $WORKDIR/artisan storage:link
php $WORKDIR/artisan optimize

#############################
## COMMAND
#############################

runEntrypoints "$@"
