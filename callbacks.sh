#!/bin/bash

var=$1
shift

case $var in
    -l|--list)
        echo "lifecycle";
        exit 0;
    ;;
    -h|--type)
        type=$1 ; shift
        if [ $type == "lifecycle" ]; then
            type=$1 ; shift
            echo "$type" > /tmp/lc.txt
            env >> /tmp/lc.txt
            if [ $type == "startup" ]; then
                $MEDIADIR/plugins/TwilioControl/TwilioPoll.php &
            else
                touch /tmp/STOPTWILIO
            fi
        fi
        exit 0
    ;;
    *)
        printf "Unknown option %s\n" "$var"
        exit 1
    ;;
esac
