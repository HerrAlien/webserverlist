<?php

require_once "inc/serversdb.php";

    $game = $_REQUEST['game'];
    
    $db = serversDB::getInstance($game);
    $servers = $db->getServers();
    echo count(array_keys($servers));
    foreach (array_keys($servers) as $server)
    {
        echo $server.'
';
    }
    
