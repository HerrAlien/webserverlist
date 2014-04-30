<?php

require_once "inc/serversdb.php";

    $game = $_REQUEST['game'];
    $db = serversDB::getInstance($game);
    $servers = $db->getServers();
    foreach ($servers as $server)
    {
        echo $server.'
';
    }
    
