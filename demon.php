<?php

// для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/vendor/autoload.php';

use altyysha_bot\model\Processing;
//use altyysha_bot\model\Schedule;
//
// Checking the schedule, whether someone needs to send a message
//(new  Schedule())->check();

// Reply to all messages, once per second
$minute = gmdate("i");
$processing = new  Processing();
while ($minute == gmdate("i")) {
    echo PHP_EOL . gmdate("i:s");
    $processing->check();
    sleep(1);
}

// Let's create a mailing list for the day.
//(new  Schedule())->generate();
