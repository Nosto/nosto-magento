#!/bin/bash
set -e

if [ "$1" != "" ]; then
  exec "$@"
else
  exec bash
fi