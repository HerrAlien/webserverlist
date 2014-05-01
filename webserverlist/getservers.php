<?php
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


