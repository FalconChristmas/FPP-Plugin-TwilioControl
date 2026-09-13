#!/bin/bash
FPPDIR=${FPPDIR:-/opt/fpp}
. ${FPPDIR}/scripts/common

# Visitor phone numbers / messages - nothing reads them once the plugin is gone
rm -rf "${MEDIADIR:?MEDIADIR unset}/plugindata/TwilioControl"

setSetting restartFlag 1
