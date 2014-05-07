<?php
// $time_pre = microtime(true);
header('Content-type: text/plain');

require_once "inc/serversdb.php";

$listOfServers = Array();
if (isset($_REQUEST['game']))
{
    $game = $_REQUEST['game'];

    $db = serversDB::getInstance ($game);
    if ($db)
    {
      $listOfServers = $db->getServers ();
    }
}

foreach ($listOfServers as $server)
{
    echo ($server. '
');
}

/* 
// typically 13ms
$time_end = microtime(true);
 echo '
'.($time_end - $time_pre - 1).' useconds';
*/