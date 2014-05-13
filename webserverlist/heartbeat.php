<?php
// $time_pre = microtime(true);
// mandatory
require_once "inc/qos.php";

header('Content-type: text/plain');

require_once "inc/serversdb.php";

if (isset($_REQUEST['game']) && isset($_REQUEST['port']))
{
    $game = $_REQUEST['game'];
    $port = $_REQUEST['port'];
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $db = serversDB::getInstance ($game);
    $timeout = 1800;
    if ($db)
    {
      $db->tickServer ($ip, $port);
      $timeout = $db->getHeartbeatTimeout();
    }
    
    echo $timeout;
}
/* 
// typically 13ms
$time_end = microtime(true);
 echo '
'.($time_end - $time_pre - 1).' useconds';
*/