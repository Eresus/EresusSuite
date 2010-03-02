#!/bin/sh
#
# This script sets file permissions
#
# @author Mikhail Krasilnikov <mk@procreat.ru>
#
# $Id: setperms.sh 597 2008-10-24 12:48:45Z mekras $
#

home=`dirname $0`
if [ $home = "." ]; then
	home=`pwd`
fi
home="$home/../.."

chmod a+rw "$home/cfg/settings.php"
chmod -R a+rw "$home/data"
chmod -R a+rw "$home/style"
chmod -R a+rw "$home/templates"
