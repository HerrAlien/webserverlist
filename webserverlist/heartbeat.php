<?php
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
