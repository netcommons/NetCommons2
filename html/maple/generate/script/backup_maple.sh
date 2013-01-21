#!/bin/sh
#
#  maple.sh
#  
#  command line gateway to the generators
#  CVS: $Id: maple.sh,v 1.1 2006/08/30 13:22:01 hawkring Exp $
#

if [ -z "$PHP_COMMAND" ]; then
	if [ -x "@PHP-BIN@" ]; then
		PHP_COMMAND="@PHP-BIN@"
	else
		PHP_COMMAND=php
	fi
fi

if [ -z "$MAPLE_DIR" ]; then
	MAPLE_DIR="@PEAR-DIR@/maple"
fi

MAPLE_GENERATOR="$MAPLE_DIR/generate/script/generate.php"

$PHP_COMMAND -d html_errors=off -qC $MAPLE_GENERATOR $*
