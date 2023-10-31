#!/bin/bash

/usr/bin/docker stop $(docker ps -a | awk '{print $1}'| tail -n +2)

/usr/local/bin/pmacontrol Daemon stopAll
