#!/bin/bash
#
# This script dumps MySQL database
#
# @author Mikhail Krasilnikov <mk@procreat.ru>
#

CONFIG="../cfg/main.inc"

STRING=`cat $CONFIG | grep conf.*\'user\'`
USER=`expr match "$STRING" ".*'\(.*\)';"`

STRING=`cat $CONFIG | grep conf.*\'password\'`
PASSWORD=`expr match "$STRING" ".*'\(.*\)';"`

STRING=`cat $CONFIG | grep conf.*\'name\'`
DATABASE=`expr match "$STRING" ".*'\(.*\)';"`

OPTIONS="--user=$USER --password=$PASSWORD"
OPTIONS="$OPTIONS --skip-opt"
OPTIONS="$OPTIONS --quick"
OPTIONS="$OPTIONS --force"
OPTIONS="$OPTIONS --create-options"
OPTIONS="$OPTIONS --add-drop-table"
OPTIONS="$OPTIONS --skip-add-locks"
OPTIONS="$OPTIONS --skip-disable-keys"
OPTIONS="$OPTIONS --skip-extended-insert"
OPTIONS="$OPTIONS --skip-lock-tables"
OPTIONS="$OPTIONS --skip-set-charset"
OPTIONS="$OPTIONS --skip-tz-utc"

mysqldump $OPTIONS $DATABASE --result-file=$DATABASE.sql


