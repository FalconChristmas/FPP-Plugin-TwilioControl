#!/bin/bash

# commands/descriptions.json was registered at fppd startup; without a restart
# the removed command types linger in fppd's in-memory list.
. ${FPPDIR}/scripts/common
setSetting restartFlag 1
