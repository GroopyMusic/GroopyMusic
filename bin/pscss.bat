@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../vendor/leafo/scssphp/bin/pscss
php "%BIN_TARGET%" %*
