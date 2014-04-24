<?php
class gameServerClass
{
    public $_ip;
    public $_port;
    public $_lastHeartbeatTS;
    
    public function __construct($ip, $port, $useDate = FALSE)
    {
        $this->_ip = $ip;
        $this->_port = $port;
        $this->setHeartbeatTS($useDate);
    }
    
    public function setHeartbeatTS($useDate = FALSE)
    {
        if ($useDate === FALSE)
            $this->_lastHeartbeatTS = date("U");
        else
            $this->_lastHeartbeatTS = $useDate;
    }
    
    public function getServers()
    {
    
    }
}
