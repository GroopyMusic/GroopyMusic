<?php

$path = dirname(__FILE__);
$console = $path . '/bin/console';
echo exec("/usr/local/php7.0/bin/php ".$console." scheduler:execute --env=prod -vvv");
