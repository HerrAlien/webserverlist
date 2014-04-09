<?php
class gameServerClass
{
    public $_ip;
    public $_port;
    
    public function __construct($ip, $port)
    {
        $this->_ip = $ip;
        $this->_port = $port;
    }
}
