#!/bin/bash
[ -z "$FORK" ] && { FORK=1 $0 "$@" & exit; }
sudo service core start
sudo service snake start