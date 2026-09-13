#!/bin/bash
set -e
FPPDIR=${FPPDIR:-/opt/fpp}
. ${FPPDIR}/scripts/common

# commands/descriptions.json is only picked up on fppd start on FPP < 10,
# which this branch also serves (see versions[] in pluginInfo.json)
setSetting restartFlag 1

# Runtime data (message/blacklist DB) lives here, not in config/. This script
# runs as root but the web server (which writes the DB) runs as ${FPPUSER}.
DATADIR="${MEDIADIR}/plugindata/TwilioControl"
mkdir -p "${DATADIR}"
chown "${FPPUSER}:${FPPGROUP}" "${DATADIR}"
chmod 700 "${DATADIR}" || true

# One-time move of the DB from its pre-plugindata location (-n: never clobber)
mv -n "${MEDIADIR}/config/FPP.TwilioControl.db" "${DATADIR}/" 2>/dev/null || true
chown "${FPPUSER}:${FPPGROUP}" "${DATADIR}"/*.db 2>/dev/null || true
