<?php
@require_once "gameServerClass.php";

$listOfServers = Array();

$listOfServers[] = new gameServerClass('127.0.0.1', 23);
$listOfServers[] = new gameServerClass('127.0.0.1', 24);

foreach ($listOfServers as $server)
{
    echo ('  '.$server->_ip.'  '.$server->_port . '
');
}
