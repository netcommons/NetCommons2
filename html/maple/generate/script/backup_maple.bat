@echo off

rem
rem   maple.bat
rem
rem   command line gateway to the generators
rem   CVS: $Id: maple.bat,v 1.1 2006/08/30 13:22:01 hawkring Exp $
rem

setlocal

if "%PHP_COMMAND%" == "" (
	if exist "@PHP-BIN@" (
		set PHP_COMMAND="@PHP-BIN@"
	) else (
		set PHP_COMMAND="php"
	)
)

if "%MAPLE_DIR%" == "" (
	set MAPLE_DIR="@PEAR-DIR@\maple"
)
set MAPLE_GENERATOR="%MAPLE_DIR%\generate\script\generate.php"

%PHP_COMMAND% -d html_errors=off -qC %MAPLE_GENERATOR% %*

endlocal
