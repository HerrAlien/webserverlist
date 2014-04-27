<?php

// memcached based
class serversDB
{
  private $m_timestampList;
  private $m_heartbeatTimeout;
  
  private function __construct()
  {
    $this->m_timestampList = new array();
    $this->m_heartbeatTimeout = 1800; // in seconds, 30 minutes
  }
  
  public function tickServer($ip, $port)
  {
    $key = $ip.":".$port;
    $this->m_timestampList[key] = date("U");
    $this->cleanDeadServers()
  }
  
  // unset to delete
  public function getServers()
  {
    $this->cleanDeadServers()
    return array_keys($this->m_timestampList);
  }
  
  private function cleanDeadServers()
  {
      $currentTime = date("U");
      $deadTime = $currentTime - $this->m_heartbeatTimeout;
      $len = count ($this->m_timestampList);
      for ($i = $len - 1; $i >=0; $i--)
      {
          if($deadTime >= $this->m_timestampList[$i])
            unset ($this->m_timestampList[$i]);
      }
  }
  
  public static function getInstance()
  {
    $cache = new Memcached ("webseverlist-4F7ECB99-9216-40BA-BD3F-6879742D92C0");
    $instance = $cache->get("serversDB");
    if ($instance === FALSE)
    {
      $instance = new serversDB();
      $cache->set("serversDB", $instance);
    }
    return $instance;
  }
  
}
