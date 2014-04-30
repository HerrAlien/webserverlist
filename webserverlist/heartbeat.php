<?php

require_once "inc/serversdb.php";

    $game = $_REQUEST['game'];
    $port = $_REQUEST['port'];
    $IP = $_SERVER['REMOTE_ADDR'];
    
    $db = serversDB::getInstance($game);
    $db->tickServer($IP, $port);
