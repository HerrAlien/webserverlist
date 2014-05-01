<?php

// memcached based
class serversDB
{
  private $m_timestampList;
  private $m_heartbeatTimeout;
  private $m_game;
  
  private function __construct()
  {
    $this->m_timestampList = array();
    $this->m_heartbeatTimeout = 1800; // in seconds, 30 minutes
  }
  
  public function getHeartbeatTimeout()
  {
    return $this->m_heartbeatTimeout;
  }
  
  public function tickServer($ip, $port)
  {
    $key = $ip.":".$port;
    $this->m_timestampList[$key] = date("U");
    $this->cleanDeadServers();
  }
  
  // unset to delete
  public function getServers()
  {
    $this->cleanDeadServers();
    return array_keys($this->m_timestampList);
  }
  
  private function cleanDeadServers()
  {
      $currentTime = date("U");
      $deadTime = $currentTime - $this->m_heartbeatTimeout;
      $len = count ($this->m_timestampList);
      $keys = array_keys($this->m_timestampList);
      for ($i = $len - 1; $i >=0; $i--)
      {
          if($deadTime >= $this->m_timestampList[$keys[$i]])
            unset ($this->m_timestampList[$keys[$i]]);
      }
      $this->save();
  }
  
  private static function getCache($game)
  {
      return new Memcached ($game."-webseverlist-4F7ECB99-9216-40BA-BD3F-6879742D92C0");
  }
  
  private static function getServerDBKey()
  {
      return "serversDB";
  }
  
  public static function getInstance($game)
  {
    $cache = serversDB::getCache($game);
    $instance = $cache->get(serversDB::getServerDBKey());
    if ($instance === FALSE)
    {
        $instance = new serversDB();
        $instance->m_game = $game;
        $saved = $cache->set(serversDB::getServerDBKey(), $instance);
    }
    return $instance;
  }
  
  private function save()
  {
    $cache = serversDB::getCache($this->m_game);
    $cache->set(serversDB::getServerDBKey(), $this);
  }
  
}
