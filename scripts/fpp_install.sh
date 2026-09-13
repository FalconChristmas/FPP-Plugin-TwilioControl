#!/bin/bash
set -e

# This plugin ships commands/descriptions.json, which fppd only reads at its
# own startup - a freshly-installed command type stays invisible to
# playlists/schedules until fppd restarts, so ask for one.
. ${FPPDIR}/scripts/common
setSetting restartFlag 1
