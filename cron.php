<?php

$path = dirname(__FILE__);
$console = $path . '/bin/console';
echo exec("/usr/local/php7.0/bin/php ".$console." app:test_command --mail --env=prod -vvv");
